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

    public array $sortBy = ['column' => 'transactions.created_at', 'direction' => 'desc'];

    //
    public function headers(): array
    {
        return [
            ['key' => 'note_id', 'label' => '#', 'class' => 'w-1'],
            ['key' => 'transaction_date_formatted', 'label' => 'Tanggal Transaksi', 'class' => 'w-64'],
            // ['key' => 'buy_price', 'label' => 'Harga Beli', 'class' => 'w-20'],
            ['key' => 'customer_name', 'label' => 'Customer', 'sortable' => false],
            ['key' => 'grand_total', 'label' => 'Total', 'sortable' => false, 'format' => ['currency', '0,.', '']],
            ['key' => 'customer_type', 'label' => 'Tipe Transaksi', 'sortable' => true],
            ['key' => 'transaction_state', 'label' => 'Status Transaksi', 'sortable' => true],
            ['key' => 'actions', 'label' => 'Aksi', 'sortable' => false, 'class' => 'w-10 text-center']
        ];
    }

    public function trans()
    {
        return Transactions::query()
            ->selectRaw("transactions.id as id, if(customer_type = 'reseller', reseller.name, customer.name) as customer_name, 
                transactions.created_at as transaction_date_formatted, grand_total, transaction_state,
                customer_type, last_transaction_numbers.id as note_id")
            ->leftJoin('customer', 'customer.id', '=', 'customer_id')
            ->leftJoin('reseller', 'reseller.id', '=', 'reseller_id')
            ->leftJoin('last_transaction_numbers', 'last_transaction_numbers.transaction_id', '=', 'transactions.id')
            ->when(
                $this->search,
                function ($q) {
                    if (strstr($this->search, '#')) {
                        $n = explode('#', $this->search);
                        $note_id = $n[1];
                        $q->where('last_transaction_numbers.id', 'like', "$note_id%");
                    } else {
                        $q->where('customer.name', 'like', "%$this->search%");
                        $q->orWhere('reseller.name', 'like', "%$this->search%");
                    }
                }
            )
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
            <x-input placeholder="Cari..." wire:model.live.debounce="search" clearable icon="o-magnifying-glass" />
        </x-slot:middle>
        <x-slot:actions>
            <x-button label="Filters" @click="$wire.drawer = true" responsive icon="o-funnel" />
            <x-button label="Transaksi Customer" link="/transactions/create" responsive icon="o-plus" class="btn-primary" />
            <x-button label="Transaksi Reseller" link="/transactions/create-reseller" responsive icon="o-plus" class="btn-secondary" />
        </x-slot:actions>
    </x-header>

    <!-- TABLE  -->
    <x-card shadow>
        <x-table show-empty-text empty-text="Belum ada Record Data, Tambahkan melalui tombol tambah di atas!"
            with-pagination :headers="$headers" :rows="$transactions" :sort-by="$sortBy"
            link="/transactions/{id}/edit">
            @scope('cell_transaction_state', $detTrans)
            <x-badge :value="$detTrans->transaction_state == 2 ? 'Lunas' : 'Hutang'"
                class="{{ ($detTrans->transaction_state == 2) ? 'badge-primary' : 'badge-error' }} badge-soft" />
            @endscope
            @scope('cell_actions', $transaction)
            @if($transaction->transaction_state == 3)
                <x-button icon="o-pencil" link="/transactions/{{ $transaction['id'] }}/edit"
                    class="btn-ghost btn-sm text-error" />
            @else

            @endif
            {{-- <x-button icon="o-trash" wire:click="delete({{ $transaction['id'] }})" wire:confirm="Are you sure?"
                spinner class="btn-ghost btn-sm text-error" /> --}}
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