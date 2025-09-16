<?php

namespace App\Http\Controllers\Invoice;

use App\Http\Controllers\Controller;
use App\Models\LastTransactionNumber;
use App\Models\TransactionDetails;
use App\Models\Transactions;
use Illuminate\Http\Request;

class PrintInvoiceController extends Controller
{
    //
    public function index(Transactions $transaction)
    {
        // print_r($transaction);
        $transction_id = $transaction->id;
        $det_trans = TransactionDetails::where('transaction_id', $transction_id)->get();
        $transaction_number = LastTransactionNumber::all()->count();
        // foreach($det_trans as $detail) {
        //     print_r($detail);
        // }
        return view("invoices/print.blade.php", compact('transaction', 'det_trans', 'transaction_number'));
    }

    public function reseller(Transactions $transaction)
    {
        // print_r($transaction);
        $transction_id = $transaction->id;
        $det_trans = TransactionDetails::where('transaction_id', $transction_id)->get();
        $transaction_number = LastTransactionNumber::all()->count();
        // foreach($det_trans as $detail) {
        //     print_r($detail);
        // }
        return view("invoices/print-reseller.blade.php", compact('transaction', 'det_trans', 'transaction_number'));
    }
}
