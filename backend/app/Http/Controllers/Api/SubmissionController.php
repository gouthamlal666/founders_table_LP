<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\GuestSuggestion;
use App\Models\PodcastApplication;
use App\Support\TeamNotifier;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

use function Illuminate\Support\defer;

/**
 * Receives the two landing-page forms. Request fields and the JSON shape of the
 * responses ({ok, id} / {ok:false, errors:{field: message}} / {ok:false, error})
 * match what assets/js/main.js already expects, so the page needs no changes.
 */
class SubmissionController extends Controller
{
    public function application(Request $request): JsonResponse
    {
        if ($this->isBot($request)) {
            return response()->json(['ok' => true]);
        }

        $data = [
            'source' => $this->clean($request, 'source', 20) === 'popup' ? 'popup' : 'section',
            'full_name' => $this->clean($request, 'fullName', 120),
            'company' => $this->clean($request, 'company', 160),
            'role' => $this->clean($request, 'role', 120),
            'email' => $this->clean($request, 'email', 200),
            'phone' => $this->clean($request, 'phone', 30),
            'industry' => $this->clean($request, 'industry', 80),
            'linkedin' => $this->clean($request, 'linkedin', 300),
            'story' => $this->clean($request, 'story', 2000),
            'page_url' => $this->clean($request, 'pageUrl', 300),
        ];

        $errors = [];
        if ($data['full_name'] === '') $errors['fullName'] = 'Please enter your name.';
        if ($data['company'] === '') $errors['company'] = 'Please enter your company name.';
        if (! $this->isEmail($data['email'])) $errors['email'] = 'Please enter a valid email.';
        if (strlen(preg_replace('/\D+/', '', $data['phone'])) < 7) $errors['phone'] = 'Please enter a valid phone number.';
        if ($data['linkedin'] !== '' && ! $this->isUrl($data['linkedin'])) $errors['linkedin'] = 'Please enter a valid link (https://…).';
        if ($errors) {
            return response()->json(['ok' => false, 'errors' => $errors], 422);
        }

        $application = PodcastApplication::create($this->nullIfEmpty($data) + ['ip_address' => $request->ip()]);

        // Email is best-effort and runs after the visitor already has their answer.
        defer(fn () => TeamNotifier::application($application));
        Log::info('[apply] new application', ['id' => $application->id, 'company' => $application->company]);

        return response()->json(['ok' => true, 'id' => $application->id]);
    }

    public function suggestion(Request $request): JsonResponse
    {
        if ($this->isBot($request)) {
            return response()->json(['ok' => true]);
        }

        $topics = $request->input('topics');
        $data = [
            'founder_name' => $this->clean($request, 'founderName', 120),
            'linkedin' => $this->clean($request, 'linkedin', 300),
            'company_name' => $this->clean($request, 'companyName', 160),
            'founder_role' => $this->clean($request, 'founderRole', 120),
            'industry' => $this->clean($request, 'industry', 80),
            'location' => $this->clean($request, 'location', 120),
            'company_website' => $this->clean($request, 'companyWebsite', 300),
            'why' => $this->clean($request, 'why', 2000),
            'topics' => mb_substr(trim(is_array($topics) ? implode(', ', $topics) : (string) $topics), 0, 400),
            'questions' => $this->clean($request, 'questions', 2000),
            'connection' => $this->clean($request, 'connection', 80),
            'suggester_name' => $this->clean($request, 'yourName', 120),
            'suggester_email' => $this->clean($request, 'yourEmail', 200),
            'page_url' => $this->clean($request, 'pageUrl', 300),
        ];

        $errors = [];
        if ($data['founder_name'] === '') $errors['founderName'] = 'Please enter the founder’s name.';
        if ($data['company_name'] === '') $errors['companyName'] = 'Please enter the company name.';
        if ($data['why'] === '') $errors['why'] = 'Please tell us why.';
        $host = $this->isUrl($data['linkedin']) ? strtolower((string) parse_url($data['linkedin'], PHP_URL_HOST)) : '';
        if ($host !== 'linkedin.com' && ! str_ends_with($host, '.linkedin.com')) {
            $errors['linkedin'] = 'Please paste a LinkedIn profile link (linkedin.com/in/…).';
        }
        if ($data['suggester_email'] !== '' && ! $this->isEmail($data['suggester_email'])) $errors['yourEmail'] = 'Please enter a valid email.';
        if ($errors) {
            return response()->json(['ok' => false, 'errors' => $errors], 422);
        }

        $suggestion = GuestSuggestion::create($this->nullIfEmpty($data) + ['ip_address' => $request->ip()]);

        defer(fn () => TeamNotifier::suggestion($suggestion));
        Log::info('[guest] new suggestion', ['id' => $suggestion->id, 'company' => $suggestion->company_name]);

        return response()->json(['ok' => true, 'id' => $suggestion->id]);
    }

    /** Hidden "website" field: people never see it, bots fill it. Pretend success. */
    private function isBot(Request $request): bool
    {
        return trim((string) $request->input('website', '')) !== '';
    }

    private function clean(Request $request, string $key, int $max): string
    {
        $value = $request->input($key);

        return is_scalar($value) ? mb_substr(trim(str_replace("\0", '', (string) $value)), 0, $max) : '';
    }

    private function isEmail(string $value): bool
    {
        return filter_var($value, FILTER_VALIDATE_EMAIL) !== false;
    }

    private function isUrl(string $value): bool
    {
        return filter_var($value, FILTER_VALIDATE_URL) !== false
            && in_array(strtolower((string) parse_url($value, PHP_URL_SCHEME)), ['http', 'https'], true);
    }

    private function nullIfEmpty(array $data): array
    {
        return array_map(fn ($v) => $v === '' ? null : $v, $data);
    }
}
