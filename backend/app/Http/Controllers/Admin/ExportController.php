<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\GuestSuggestion;
use App\Models\PodcastApplication;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * Downloads applications or suggestions as a CSV that opens cleanly in Excel / Google Sheets.
 * Honours the same ?search= and ?status= filters as the list screens.
 */
class ExportController extends Controller
{
    private const COLUMNS = [
        'applications' => [
            'id' => 'ID', 'created_at' => 'Received', 'status' => 'Status', 'source' => 'Form', 'full_name' => 'Name',
            'company' => 'Company', 'role' => 'Role', 'email' => 'Email', 'phone' => 'Phone', 'industry' => 'Industry',
            'linkedin' => 'LinkedIn / website', 'story' => 'Story', 'notes' => 'Internal notes',
        ],
        'suggestions' => [
            'id' => 'ID', 'created_at' => 'Received', 'status' => 'Status', 'founder_name' => 'Founder',
            'linkedin' => 'LinkedIn', 'company_name' => 'Company', 'founder_role' => 'Role', 'industry' => 'Industry',
            'location' => 'Based in', 'company_website' => 'Website / Instagram', 'why' => 'Why this founder',
            'topics' => 'Topics', 'questions' => 'Questions', 'connection' => 'Connection',
            'suggester_name' => 'Suggested by', 'suggester_email' => 'Suggester email', 'notes' => 'Internal notes',
        ],
    ];

    public function __invoke(Request $request, string $type): StreamedResponse
    {
        $model = $type === 'applications' ? PodcastApplication::class : GuestSuggestion::class;
        $columns = self::COLUMNS[$type];

        $query = $model::query()
            ->search((string) $request->query('search', ''))
            ->when($request->query('status'), fn ($q, $status) => $q->where('status', $status))
            ->latest();

        $filename = "founders-table-{$type}-".now()->format('Y-m-d').'.csv';

        return response()->streamDownload(function () use ($query, $columns) {
            $out = fopen('php://output', 'w');
            fwrite($out, "\xEF\xBB\xBF"); // UTF-8 BOM so Excel shows Arabic/accents correctly
            fputcsv($out, array_values($columns));

            $query->chunk(500, function ($rows) use ($out, $columns) {
                foreach ($rows as $row) {
                    fputcsv($out, array_map(function ($key) use ($row) {
                        $value = $key === 'created_at' ? $row->created_at?->format('Y-m-d H:i') : (string) ($row->{$key} ?? '');
                        // Guard against spreadsheet formula injection from public form input.
                        // Plain numbers like "+971 50 123 4567" are safe and left as they are.
                        $isPlainNumber = preg_match('/^[+\-]?[\d\s().\-]+$/', $value);
                        return ! $isPlainNumber && preg_match('/^[=+\-@\t\r]/', $value) ? "'".$value : $value;
                    }, array_keys($columns)));
                }
            });

            fclose($out);
        }, $filename, ['Content-Type' => 'text/csv; charset=utf-8']);
    }
}
