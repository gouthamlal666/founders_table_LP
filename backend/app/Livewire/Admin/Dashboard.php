<?php

namespace App\Livewire\Admin;

use App\Models\GuestSuggestion;
use App\Models\PodcastApplication;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;

#[Layout('layouts.admin')]
#[Title('Dashboard')]
class Dashboard extends Component
{
    /** Chart window in days. */
    #[Url]
    public int $range = 30;

    public function updatedRange(): void
    {
        $this->range = in_array($this->range, [7, 30, 90], true) ? $this->range : 30;
    }

    #[Computed]
    public function stats(): array
    {
        $weekAgo = now()->subDays(7);
        $twoWeeksAgo = now()->subDays(14);

        $count = fn (string $model, ?Carbon $from = null, ?Carbon $to = null) => $model::query()
            ->when($from, fn ($q) => $q->where('created_at', '>=', $from))
            ->when($to, fn ($q) => $q->where('created_at', '<', $to))
            ->count();

        $appsWeek = $count(PodcastApplication::class, $weekAgo);
        $appsPrev = $count(PodcastApplication::class, $twoWeeksAgo, $weekAgo);
        $suggWeek = $count(GuestSuggestion::class, $weekAgo);
        $suggPrev = $count(GuestSuggestion::class, $twoWeeksAgo, $weekAgo);

        return [
            'applications' => PodcastApplication::count(),
            'suggestions' => GuestSuggestion::count(),
            'newApplications' => PodcastApplication::where('status', 'new')->count(),
            'newSuggestions' => GuestSuggestion::where('status', 'new')->count(),
            'scheduled' => PodcastApplication::whereIn('status', ['scheduled', 'recorded'])->count(),
            'week' => $appsWeek + $suggWeek,
            'weekDelta' => ($appsWeek + $suggWeek) - ($appsPrev + $suggPrev),
        ];
    }

    /** One bar per day for the chosen range, split into applications and suggestions. */
    #[Computed]
    public function chart(): array
    {
        $from = now()->startOfDay()->subDays($this->range - 1);
        $perDay = fn (string $model) => $model::where('created_at', '>=', $from)
            ->get(['created_at'])
            ->countBy(fn ($row) => $row->created_at->format('Y-m-d'));

        $apps = $perDay(PodcastApplication::class);
        $sugg = $perDay(GuestSuggestion::class);

        $days = collect(range(0, $this->range - 1))->map(function ($i) use ($from, $apps, $sugg) {
            $date = $from->copy()->addDays($i);
            $key = $date->format('Y-m-d');

            return ['date' => $date, 'apps' => $apps[$key] ?? 0, 'sugg' => $sugg[$key] ?? 0];
        });

        return [
            'days' => $days,
            'max' => max(1, $days->max(fn ($d) => $d['apps'] + $d['sugg'])),
            'total' => $days->sum(fn ($d) => $d['apps'] + $d['sugg']),
        ];
    }

    /** Where every application sits in the pipeline. */
    #[Computed]
    public function pipeline(): Collection
    {
        $counts = PodcastApplication::selectRaw('status, count(*) as total')->groupBy('status')->pluck('total', 'status');

        return collect(PodcastApplication::STATUSES)->map(fn ($label, $key) => ['key' => $key, 'label' => $label, 'total' => (int) ($counts[$key] ?? 0)]);
    }

    #[Computed]
    public function industries(): Collection
    {
        return PodcastApplication::whereNotNull('industry')
            ->selectRaw('industry, count(*) as total')->groupBy('industry')->orderByDesc('total')->limit(5)->get();
    }

    #[Computed]
    public function sources(): array
    {
        return [
            'section' => PodcastApplication::where('source', 'section')->count(),
            'popup' => PodcastApplication::where('source', 'popup')->count(),
        ];
    }

    #[Computed]
    public function latestApplications(): Collection
    {
        return PodcastApplication::latest()->limit(5)->get();
    }

    #[Computed]
    public function latestSuggestions(): Collection
    {
        return GuestSuggestion::latest()->limit(5)->get();
    }

    public function render()
    {
        return view('livewire.admin.dashboard');
    }
}
