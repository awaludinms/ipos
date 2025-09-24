<?php

use Livewire\Volt\Component;
use App\Models\Expenses;
use Illuminate\Database\Eloquent\Builder;
use Livewire\WithPagination;
use Mary\Traits\Toast;

new class extends Component {
    //
    use WithPagination; 
    use Toast;

    public string $search = '';

    public bool $drawer = false;

    public array $sortBy = ['column' => 'id', 'direction' => 'asc'];
    //

    public function expenses()
    {
        return Expenses::query()
            ->selectRaw('*, expenses_date as expenses_date_formatted, expense_value as expense_value_formatted')
            ->when($this->search, fn(Builder $q) => $q->where('description', 'like', "%$this->search%"))
            ->orderBy(...array_values($this->sortBy))
            ->paginate(10);
    }    

    public function headers(): array
    {
        return [
            ['key' => 'id', 'label' => '#', 'class' => 'w-1'],
            ['key' => 'expenses_date_formatted', 'label' => 'Tanggal', 'class' => 'w-50'],
            ['key' => 'description', 'label' => 'Deskripsi', 'class' => 'w-64'],
            ['key' => 'person', 'label' => 'Personal', 'class' => 'w-64'],
            ['key' => 'expense_value_formatted', 'label' => 'Nilai Pengeluaran', 'class' => 'w-64'],
        ];
    }

    // Delete action
    public function delete($id): void
    {
        $this->warning("Will delete #$id", 'It is fake.', position: 'toast-bottom');
        Expenses::find($id)->delete();
    }
    
    public function with(): array
    {
        return [
            'expenses' => $this->expenses(),
            'headers' => $this->headers()
        ];
    }
}; ?>

<div>
    <!-- HEADER -->
    <x-header title="Pengeluaran" separator progress-indicator>
        <x-slot:middle class="!justify-end">
            <x-input placeholder="Cari..." wire:model.live.debounce="search" clearable icon="o-magnifying-glass" />
        </x-slot:middle>
        <x-slot:actions>
            <x-button label="Filters" @click="$wire.drawer = true" responsive icon="o-funnel" />
            <x-button label="Tambah" link="/expenses/create" responsive icon="o-plus" class="btn-primary" />
        </x-slot:actions>
    </x-header>

    <!-- TABLE  -->
    <x-card shadow>
        <x-table show-empty-text empty-text="Belum ada Record Data, Tambahkan melalui tombol tambah di atas!"  :headers="$headers" :rows="$expenses" :sort-by="$sortBy" link="/expenses/{id}/edit">
            @scope('actions', $user)
            {{-- <x-button icon="o-pencil" wire:click="delete({{ $user['id'] }})" wire:confirm="Are you sure?" spinner
                --}} {{-- class="btn-ghost btn-sm text-error" /> --}}
            <x-button icon="o-trash" wire:click="delete({{ $user['id'] }})" wire:confirm="Are you sure?" spinner
                class="btn-ghost btn-sm text-error" />
            @endscope
        </x-table>
    </x-card>

    <!-- FILTER DRAWER -->
    <x-drawer wire:model="drawer" title="Filters" right separator with-close-button class="lg:w-1/3">
        <x-input placeholder="Cari..." wire:model.live.debounce="search" icon="o-magnifying-glass"
            @keydown.enter="$wire.drawer = false" />

        <x-slot:actions>
            <x-button label="Reset" icon="o-x-mark" wire:click="clear" spinner />
            <x-button label="Done" icon="o-check" class="btn-primary" @click="$wire.drawer = false" />
        </x-slot:actions>
    </x-drawer>


</div>