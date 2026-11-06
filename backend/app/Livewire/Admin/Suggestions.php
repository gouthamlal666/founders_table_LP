<?php

namespace App\Livewire\Admin;

use App\Livewire\Admin\Concerns\ListsSubmissions;
use App\Models\GuestSuggestion;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.admin')]
#[Title('Guest suggestions')]
class Suggestions extends Component
{
    use ListsSubmissions;

    protected const MODEL = GuestSuggestion::class;
    protected const EXPORT_TYPE = 'suggestions';

    public function render()
    {
        return view('livewire.admin.suggestions', [
            'suggestions' => $this->filteredQuery()->paginate(15),
            'statuses' => GuestSuggestion::STATUSES,
            'counts' => $this->statusCounts(),
            'total' => GuestSuggestion::count(),
            'exportUrl' => $this->exportUrl(),
        ]);
    }
}
