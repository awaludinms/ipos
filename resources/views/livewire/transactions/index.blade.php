<?php

use App\Models\Transactions;
use Livewire\Volt\Component;
use Illuminate\Database\Eloquent\Builder;
use Livewire\WithPagination;
use Mary\Traits\Toast;

new class extends Component {
    use WithPagination; 
    use Toast;

    public string $search = '';

    public bool $drawer = false;

    public array $sortBy = ['column' => 'transactions.id', 'direction' => 'asc'];
    
    //
    public function headers(): array
    {
        return [
            ['key' => 'id', 'label' => '#', 'class' => 'w-1'],
            ['key' => 'transaction_date_formatted', 'label' => 'Tanggal Transaksi', 'class' => 'w-64'],
            // ['key' => 'buy_price', 'label' => 'Harga Beli', 'class' => 'w-20'],
            ['key' => 'customer_name', 'label' => 'Customer', 'sortable' => false],
            ['key' => 'grand_total', 'label' => 'Total', 'sortable' => false, 'format' => ['currency', '0,.', '']],
            ['key' => 'customer_type', 'label' => 'Tipe Transaksi', 'sortable' => false],
        ];
    }

    public function trans()
    {
        return Transactions::query()
            ->selectRaw("transactions.id as id, if(customer_type = 'reseller', reseller.name, customer.name) as customer_name, transactions.created_at as transaction_date_formatted, grand_total, customer_type")
            ->leftJoin('customer', 'customer.id', '=', 'customer_id')
            ->leftJoin('reseller', 'reseller.id', '=', 'reseller_id')
            ->when($this->search, fn(Builder $q) => $q->where('product_name', 'like', "%$this->search%"))
            ->where('transaction_state', '>', 1)
            ->orderBy(...array_values($this->sortBy))
            ->paginate(10);
    }

    public function with(): array
    {
        return [
            'headers' => $this->headers(),
            'transactions' => $this->trans()
        ];
    }

    // public function delete($id): void
    // {
    //     $product = Transactions::find($id);
    //     $nama_product = $product->product_name;
    //     try {
    //         $product->delete();
    //         $this->success("Data Produk " . $nama_product . " berhasil dihapus", 'Data berhasil dihapus', position: 'toast-bottom');
    //     } catch (\Exception $e) {
    //         $this->warning("Data Produk " . $nama_product . " gagal dihapus", 'Data berhasil dihapus', position: 'toast-bottom');
    //     }
    // }
}; ?>

<div>

    <!-- HEADER -->
    <x-header title="Transaksi" separator progress-indicator>
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
        <x-table with-pagination :headers="$headers" :rows="$transactions" :sort-by="$sortBy"
            link="/transactions/{id}/detail">
            @scope('actions', $transaction)
            {{-- <x-button icon="o-pencil" wire:click="delete({{ $transaction['id'] }})" wire:confirm="Are you sure?" spinner
                --}} {{-- class="btn-ghost btn-sm text-error" /> --}}
            {{-- <x-button icon="o-trash" wire:click="delete({{ $transaction['id'] }})" wire:confirm="Are you sure?" spinner
                class="btn-ghost btn-sm text-error" /> --}}
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