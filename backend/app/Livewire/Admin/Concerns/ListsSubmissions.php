<?php

namespace App\Livewire\Admin\Concerns;

use Livewire\Attributes\Url;
use Livewire\WithPagination;

/**
 * Shared behaviour for the applications and suggestions lists:
 * search, status filter, sorting and inline status change.
 * The using component defines MODEL (the Eloquent class).
 */
trait ListsSubmissions
{
    use WithPagination;

    #[Url(except: '')]
    public string $search = '';

    #[Url(except: '')]
    public string $status = '';

    #[Url(except: 'newest')]
    public string $sort = 'newest';

    public function updated($property): void
    {
        if (in_array($property, ['search', 'status', 'sort', 'source'], true)) {
            $this->resetPage();
        }
    }

    public function clearFilters(): void
    {
        $this->reset('search', 'status', 'sort');
        if (property_exists($this, 'source')) {
            $this->reset('source');
        }
        $this->resetPage();
    }

    public function setStatus(int $id, string $status): void
    {
        $model = static::MODEL;
        abort_unless(array_key_exists($status, $model::STATUSES), 422);

        $model::findOrFail($id)->update(['status' => $status]);
        $this->dispatch('toast', message: 'Status changed to '.$model::STATUSES[$status]);
        $this->dispatch('submissions-changed');
    }

    protected function filteredQuery()
    {
        $model = static::MODEL;

        return $model::query()
            ->search($this->search)
            ->when($this->status !== '' && array_key_exists($this->status, $model::STATUSES), fn ($q) => $q->where('status', $this->status))
            ->orderBy('created_at', $this->sort === 'oldest' ? 'asc' : 'desc');
    }

    protected function statusCounts(): array
    {
        $model = static::MODEL;

        return $model::selectRaw('status, count(*) as total')->groupBy('status')->pluck('total', 'status')->all();
    }

    protected function exportUrl(): string
    {
        return route('admin.export', array_filter([
            'type' => static::EXPORT_TYPE, 'search' => $this->search, 'status' => $this->status,
        ]));
    }
}
