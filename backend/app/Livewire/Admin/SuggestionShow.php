<?php

namespace App\Livewire\Admin;

use App\Models\GuestSuggestion;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Validate;
use Livewire\Component;

#[Layout('layouts.admin')]
class SuggestionShow extends Component
{
    public GuestSuggestion $suggestion;

    public string $status = '';

    #[Validate('nullable|string|max:5000')]
    public string $notes = '';

    public function mount(GuestSuggestion $suggestion): void
    {
        $this->suggestion = $suggestion;
        $this->status = $suggestion->status;
        $this->notes = (string) $suggestion->notes;
    }

    public function updatedStatus(string $value): void
    {
        abort_unless(array_key_exists($value, GuestSuggestion::STATUSES), 422);
        $this->suggestion->update(['status' => $value]);
        $this->dispatch('toast', message: 'Status changed to '.GuestSuggestion::STATUSES[$value]);
        $this->dispatch('submissions-changed');
    }

    public function saveNotes(): void
    {
        $this->validate();
        $this->suggestion->update(['notes' => $this->notes === '' ? null : $this->notes]);
        $this->dispatch('toast', message: 'Notes saved');
    }

    public function delete()
    {
        $this->suggestion->delete();
        session()->flash('toast', 'Suggestion deleted');

        return $this->redirectRoute('admin.suggestions', navigate: true);
    }

    public function render()
    {
        return view('livewire.admin.suggestion-show', [
            'statuses' => GuestSuggestion::STATUSES,
        ])->title($this->suggestion->founder_name);
    }
}
