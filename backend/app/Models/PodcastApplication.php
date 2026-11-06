<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class PodcastApplication extends Model
{
    /** Pipeline stages shown in the admin, in order. */
    public const STATUSES = [
        'new' => 'New',
        'contacted' => 'Contacted',
        'shortlisted' => 'Shortlisted',
        'scheduled' => 'Scheduled',
        'recorded' => 'Recorded',
        'declined' => 'Declined',
    ];

    protected $fillable = [
        'source', 'full_name', 'company', 'role', 'email', 'phone', 'industry',
        'linkedin', 'story', 'page_url', 'status', 'notes', 'ip_address',
    ];

    protected $hidden = ['ip_address'];

    public function scopeSearch(Builder $query, string $term): Builder
    {
        $term = trim($term);
        if ($term === '') {
            return $query;
        }

        return $query->where(function (Builder $q) use ($term) {
            foreach (['full_name', 'company', 'email', 'phone', 'industry', 'role'] as $column) {
                $q->orWhere($column, 'like', "%{$term}%");
            }
        });
    }

    public function statusLabel(): string
    {
        return self::STATUSES[$this->status] ?? ucfirst($this->status);
    }

    /** Digits-only number for wa.me links. */
    public function whatsappNumber(): string
    {
        return preg_replace('/\D+/', '', $this->phone);
    }
}
