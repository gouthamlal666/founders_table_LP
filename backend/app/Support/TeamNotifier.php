<?php

namespace App\Support;

use App\Models\GuestSuggestion;
use App\Models\PodcastApplication;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Throwable;

/**
 * Emails the team about a new submission. Best-effort: the submission is already
 * saved, so a mail failure is logged and never shown to the visitor.
 * Set FOUNDERS_MAIL_TO (and the MAIL_* settings) in .env to turn it on.
 */
class TeamNotifier
{
    public static function application(PodcastApplication $a): void
    {
        self::send(
            subject: "Founders Table — guest application: {$a->full_name} ({$a->company})",
            kicker: 'Founders Table · Guest application',
            title: "{$a->full_name} — {$a->company}",
            rows: [
                'Name' => $a->full_name, 'Company' => $a->company, 'Role' => $a->role, 'Email' => $a->email,
                'Phone' => $a->phone, 'Industry' => $a->industry, 'LinkedIn / website' => $a->linkedin,
                'Their story' => $a->story, 'Form' => $a->source === 'popup' ? 'Pop-up' : 'Page section',
            ],
            replyTo: $a->email,
            adminUrl: route('admin.applications.show', $a),
        );
    }

    public static function suggestion(GuestSuggestion $s): void
    {
        self::send(
            subject: "Founders Table — guest suggestion: {$s->founder_name} ({$s->company_name})",
            kicker: 'Founders Table · Guest suggestion',
            title: "New guest suggestion: {$s->founder_name}",
            rows: [
                'Founder' => $s->founder_name, 'LinkedIn' => $s->linkedin, 'Company' => $s->company_name,
                'Role' => $s->founder_role, 'Industry' => $s->industry, 'Based in' => $s->location,
                'Website / Instagram' => $s->company_website, 'Why this founder' => $s->why, 'Topics' => $s->topics,
                'Questions to ask' => $s->questions, 'Connection' => $s->connection,
                'Suggested by' => $s->suggester_name ?: 'Anonymous', 'Email' => $s->suggester_email,
            ],
            replyTo: $s->suggester_email,
            adminUrl: route('admin.suggestions.show', $s),
        );
    }

    private static function send(string $subject, string $kicker, string $title, array $rows, ?string $replyTo, string $adminUrl): void
    {
        $to = config('services.founders.mail_to');
        if (! $to) {
            Log::info('[email] FOUNDERS_MAIL_TO not set — skipped notification', ['subject' => $subject]);
            return;
        }

        try {
            $html = view('mail.submission', compact('kicker', 'title', 'rows', 'adminUrl'))->render();
            Mail::html($html, function ($message) use ($to, $subject, $replyTo) {
                $message->to(array_map('trim', explode(',', $to)))->subject($subject);
                if ($replyTo) {
                    $message->replyTo($replyTo);
                }
            });
        } catch (Throwable $e) {
            Log::error('[email] notification failed', ['subject' => $subject, 'error' => $e->getMessage()]);
        }
    }
}
