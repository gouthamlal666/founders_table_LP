<?php

namespace App\Livewire\Admin;

use App\Livewire\Admin\Concerns\ListsSubmissions;
use App\Models\PodcastApplication;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;

#[Layout('layouts.admin')]
#[Title('Guest applications')]
class Applications extends Component
{
    use ListsSubmissions;

    protected const MODEL = PodcastApplication::class;
    protected const EXPORT_TYPE = 'applications';

    /** section | popup */
    #[Url(except: '')]
    public string $source = '';

    public function render()
    {
        $applications = $this->filteredQuery()
            ->when(in_array($this->source, ['section', 'popup'], true), fn ($q) => $q->where('source', $this->source))
            ->paginate(15);

        return view('livewire.admin.applications', [
            'applications' => $applications,
            'statuses' => PodcastApplication::STATUSES,
            'counts' => $this->statusCounts(),
            'total' => PodcastApplication::count(),
            'exportUrl' => $this->exportUrl(),
        ]);
    }
}
