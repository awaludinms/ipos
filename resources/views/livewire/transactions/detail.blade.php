<?php

use App\Models\Transactions;
use App\Models\TransactionDetails;
use Livewire\Volt\Component;

new class extends Component {
    public Transactions $transaction;

    public array $sortBy = ['column' => 'id', 'direction' => 'asc'];

    public function detailTransaction()
    {
        return TransactionDetails::query()
            ->selectRaw("transaction_details.id, IF(product_id is null, concat_ws('',transaction_details.product_name, ' \n<div class=\"tab\">', notes, '</div>'), concat_ws('',products.product_name, ' \n<div class=\"tab\">', notes, '</div>')) as product_name, product_qty, product_price, product_subtotal, notes")
            ->leftJoin('products', 'product_id', '=', 'products.id')
            ->where('transaction_id', $this->transaction->id)->get();
     }

    public function headersDetTrans(): array
    {
        return [
            ['key' => 'product_name', 'label' => 'Nama Produk', 'class' => 'w-64'],
            // ['key' => 'buy_price', 'label' => 'Harga Beli', 'class' => 'w-20'],
            ['key' => 'product_qty', 'label' => 'Qty', 'sortable' => false, 'class' => 'w-10'],
            ['key' => 'product_price', 'label' => 'Harga', 'sortable' => false, 'format' => ['currency', '0,.', '']],
            ['key' => 'product_subtotal', 'label' => 'Sub Total', 'sortable' => false, 'format' => ['currency', '0,.', '']],
        ];
    }


    public function with()
    {
        return [
            'detailtransaksi' => $this->detailTransaction(),
            'headersDetTrans' => $this->headersDetTrans(),
        ];
    }
}; ?>

<div>
    <x-header title="Transaksi" separator>
        <x-slot:actions>
            <div class="gap-3">
                @if($edit_status)
                    <x-button label="Batal Ubah Transaksi"
                        wire:confirm="Anda ingin membatalkan perubahan pada transaksi ini?" wire:click="removeAllChanges"
                        icon="o-x-mark" class="btn-error" />
                @else
                    <x-button label="Kembali" link="/transactions" icon="o-arrow-left" class="btn-secondary" />
                    <x-button label="Ubah" @click="$wire.myModalConfirm = true" icon="o-pencil" class="btn-primary" />
                @endif
                @if($edit_status)
                    <x-button label="Bayar" @click="$wire.myModal2 = true" icon="o-paper-airplane" class="btn-success"
                        spinner />
                @else
                    <x-button disabled label="Bayar" @click="$wire.myModal2 = true" icon="o-paper-airplane"
                        class="btn-success" spinner />
                @endif
            </div>
        </x-slot:actions>
    </x-header>

    <!-- TABLE  -->
    <x-card shadow>
        <x-table show-empty-text empty-text="Belum ada Record Data, Tambahkan melalui tombol tambah di atas!"
            :headers="$headers" :rows="$transactions" :sort-by="$sortBy">
            @scope('cell_transaction_state', $detTrans)
            <x-badge :value="$detTrans->transaction_state == 2 ? 'Lunas' : 'Hutang'"
                class="{{ ($detTrans->transaction_state == 2) ? 'badge-primary' : 'badge-error' }} badge-soft" />
            @endscope
            {{-- @scope('cell_actions', $transaction)
            @if($transaction->transaction_state == 3)
            <x-button icon="o-pencil" link="/transactions/{{ $transaction['id'] }}/edit"
                class="btn-ghost btn-sm text-error" />
            @endif --}}
            {{-- <x-button icon="o-trash" wire:click="delete({{ $transaction['id'] }})" wire:confirm="Are you sure?"
                spinner class="btn-ghost btn-sm text-error" /> --}}
            {{-- @endscope --}}
        </x-table>
    </x-card>


    <x-header title="Transaksi Detail" separator progress-indicator>
        <x-slot:actions>
            <div class="gap-3">
                <x-button label="Tambah Item" @click="$wire.myModal1 = true" icon="o-plus" class="btn-primary" />
                <x-button disabled label="Bayar" @click="$wire.myModal2 = true" icon="o-paper-airplane"
                    class="btn-success" spinner />
            </div>
        </x-slot:actions>
    </x-header>

    <!-- TABLE  -->
    <x-card shadow class="sm:p-0">
        <x-table show-empty-text empty-text="Belum ada Record Data, Tambahkan melalui tombol tambah di atas!"
            :headers="$headersDetTrans" :rows="$detailtransaksi" :sort-by="$sortBy">
            {{-- @row-click="$wire.addNote($event.detail.id)"> --}}
            @scope('cell_product_name', $detTrans)
            {!! nl2br($detTrans->product_name) !!}
            @endscope
            
        </x-table>
    </x-card>
</div>