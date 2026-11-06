<?php

namespace App\Livewire\Admin;

use App\Models\PodcastApplication;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Validate;
use Livewire\Component;

#[Layout('layouts.admin')]
class ApplicationShow extends Component
{
    public PodcastApplication $application;

    public string $status = '';

    #[Validate('nullable|string|max:5000')]
    public string $notes = '';

    public function mount(PodcastApplication $application): void
    {
        $this->application = $application;
        $this->status = $application->status;
        $this->notes = (string) $application->notes;
    }

    public function updatedStatus(string $value): void
    {
        abort_unless(array_key_exists($value, PodcastApplication::STATUSES), 422);
        $this->application->update(['status' => $value]);
        $this->dispatch('toast', message: 'Status changed to '.PodcastApplication::STATUSES[$value]);
        $this->dispatch('submissions-changed');
    }

    public function saveNotes(): void
    {
        $this->validate();
        $this->application->update(['notes' => $this->notes === '' ? null : $this->notes]);
        $this->dispatch('toast', message: 'Notes saved');
    }

    public function delete()
    {
        $this->application->delete();
        session()->flash('toast', 'Application deleted');

        return $this->redirectRoute('admin.applications', navigate: true);
    }

    public function render()
    {
        return view('livewire.admin.application-show', [
            'statuses' => PodcastApplication::STATUSES,
        ])->title($this->application->full_name);
    }
}
