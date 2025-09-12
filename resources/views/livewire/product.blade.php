<?php

use App\Models\Product;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Rule;
use Livewire\Attributes\Title;
use Livewire\Volt\Component;
use Illuminate\Database\Eloquent\Builder;
use Livewire\WithPagination;

new class extends Component {
    use WithPagination; 

    public string $search = '';

    public bool $drawer = false;

    public array $sortBy = ['column' => 'id', 'direction' => 'asc'];
    //
    public function products()
    {
        return Product::query()->selectRaw('id, product_name, customer_price as customer_price_formatted, reseller_price as reseller_price_formatted, product_type_id')
            ->when($this->search, fn(Builder $q) => $q->where('product_name', 'like', "%$this->search%"))
            ->orderBy(...array_values($this->sortBy))
            ->paginate(10);
    }

    public function headers(): array
    {
        return [
            ['key' => 'id', 'label' => '#', 'class' => 'w-1'],
            ['key' => 'product_name', 'label' => 'Nama Produk', 'class' => 'w-64'],
            // ['key' => 'buy_price', 'label' => 'Harga Beli', 'class' => 'w-20'],
            ['key' => 'customer_price_formatted', 'label' => 'Harga Jual Customer', 'sortable' => false],
            ['key' => 'reseller_price_formatted', 'label' => 'Harga Jual Reseller', 'sortable' => false],
            ['key' => 'product_type.name', 'label' => 'Kategori', 'sortable' => false],
        ];
    }

    public function with(): array
    {
        return [
            'products' => $this->products(),
            'headers' => $this->headers()
        ];
    }

}; ?>

<div>
    <!-- HEADER -->
    <x-header title="Produk" separator progress-indicator>
        <x-slot:middle class="!justify-end">
            <x-input placeholder="Search..." wire:model.live.debounce="search" clearable icon="o-magnifying-glass" />
        </x-slot:middle>
        <x-slot:actions>
            <x-button label="Filters" @click="$wire.drawer = true" responsive icon="o-funnel" />
            <x-button label="Tambah" link="/products/create" responsive icon="o-plus" class="btn-primary" />
        </x-slot:actions>
    </x-header>

    <!-- TABLE  -->
    <x-card shadow>
        <x-table :headers="$headers" :rows="$products" :sort-by="$sortBy" link="/products/{id}/edit">
            @scope('actions', $user)
            {{-- <x-button icon="o-pencil" wire:click="delete({{ $user['id'] }})" wire:confirm="Are you sure?" spinner --}}
            {{-- class="btn-ghost btn-sm text-error" /> --}}
            <x-button icon="o-trash" wire:click="delete({{ $user['id'] }})" wire:confirm="Are you sure?" spinner
                class="btn-ghost btn-sm text-error" />
            @endscope
        </x-table>
    </x-card>

    <!-- FILTER DRAWER -->
    <x-drawer wire:model="drawer" title="Filters" right separator with-close-button class="lg:w-1/3">
        <x-input placeholder="Search..." wire:model.live.debounce="search" icon="o-magnifying-glass"
            @keydown.enter="$wire.drawer = false" />

        <x-slot:actions>
            <x-button label="Reset" icon="o-x-mark" wire:click="clear" spinner />
            <x-button label="Done" icon="o-check" class="btn-primary" @click="$wire.drawer = false" />
        </x-slot:actions>
    </x-drawer>
</div>