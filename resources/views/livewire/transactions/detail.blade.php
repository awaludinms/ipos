<?php

use App\Models\Transactions;
use App\Models\TransactionDetails;
use Livewire\Volt\Component;

new class extends Component {
    public Transactions $transaction;

    public function detailTransaction()
    {
        return TransactionDetails::where('transaction_id', $this->transaction->id)->get();
    }

    public function with()
    {
        return [
            'detail_transaksi' => $this->detailTransaction()
        ];
    }
}; ?>

<div>
    <x-header title="Transaksi" separator />


    <x-header title="Detail Transaksi" separator />


</div>