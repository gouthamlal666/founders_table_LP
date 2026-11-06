<?php

namespace App\Livewire\Admin;

use App\Models\GuestSuggestion;
use App\Models\PodcastApplication;
use Livewire\Attributes\On;
use Livewire\Component;

/**
 * Sidebar links with "new" counters. Re-renders whenever a screen dispatches
 * "submissions-changed", so the badges stay right without a page reload.
 */
class SidebarNav extends Component
{
    /** Route name of the page this sidebar sits on (for the active state). */
    public string $current = '';

    public function mount(): void
    {
        $this->current = (string) request()->route()?->getName();
    }

    #[On('submissions-changed')]
    public function refresh(): void
    {
        // Re-render is enough; counts are read in render().
    }

    public function render()
    {
        return view('livewire.admin.sidebar-nav', [
            'items' => [
                ['route' => 'admin.dashboard', 'match' => 'admin.dashboard', 'label' => 'Dashboard', 'icon' => 'grid', 'badge' => null],
                ['route' => 'admin.applications', 'match' => 'admin.applications', 'label' => 'Guest applications', 'icon' => 'mic',
                    'badge' => PodcastApplication::where('status', 'new')->count()],
                ['route' => 'admin.suggestions', 'match' => 'admin.suggestions', 'label' => 'Guest suggestions', 'icon' => 'users',
                    'badge' => GuestSuggestion::where('status', 'new')->count()],
                ['route' => 'admin.settings', 'match' => 'admin.settings', 'label' => 'Account', 'icon' => 'settings', 'badge' => null],
            ],
        ]);
    }
}
