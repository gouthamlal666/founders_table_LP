<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class GuestSuggestion extends Model
{
    /** Pipeline stages shown in the admin, in order. */
    public const STATUSES = [
        'new' => 'New',
        'reviewing' => 'Reviewing',
        'contacted' => 'Contacted',
        'invited' => 'Invited',
        'declined' => 'Declined',
    ];

    protected $fillable = [
        'founder_name', 'linkedin', 'company_name', 'founder_role', 'industry', 'location',
        'company_website', 'why', 'topics', 'questions', 'connection',
        'suggester_name', 'suggester_email', 'page_url', 'status', 'notes', 'ip_address',
    ];

    protected $hidden = ['ip_address'];

    public function scopeSearch(Builder $query, string $term): Builder
    {
        $term = trim($term);
        if ($term === '') {
            return $query;
        }

        return $query->where(function (Builder $q) use ($term) {
            foreach (['founder_name', 'company_name', 'industry', 'location', 'suggester_name', 'suggester_email'] as $column) {
                $q->orWhere($column, 'like', "%{$term}%");
            }
        });
    }

    public function statusLabel(): string
    {
        return self::STATUSES[$this->status] ?? ucfirst($this->status);
    }
}
