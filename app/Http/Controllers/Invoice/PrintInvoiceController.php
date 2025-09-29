<?php

namespace App\Http\Controllers\Invoice;

use App\Http\Controllers\Controller;
use App\Models\LastTransactionNumber;
use App\Models\TransactionDetails;
use App\Models\TransactionPayments;
use App\Models\TransactionReceipts;
use App\Models\Transactions;
use Auth;
use Barryvdh\DomPDF\Facade\Pdf;
use DB;

class PrintInvoiceController extends Controller
{
    //
    public function index(Transactions $transaction)
    {
        // print_r($transaction);
        $transction_id = $transaction->id;
        $det_trans = TransactionDetails::query()
            ->leftJoin('products', 'product_id', '=', 'products.id')
            ->selectRaw("transaction_details.id, IF(product_id is null, concat_ws('',transaction_details.product_name, '\n', '<div class=\"tab\">', notes, '</div>'), concat_ws('',products.product_name, '\n', '<div class=\"tab\">', notes, '</div>')) as product_name, product_qty, product_price, product_subtotal, notes")
            ->where('transaction_id', $transction_id)->get();
        $transaction_number = LastTransactionNumber::where('transaction_id', $transction_id)->first()->id;
        // foreach($det_trans as $detail) {
        //     print_r($detail);
        // }
        $type = 'print';

        $trans_payemnt = TransactionPayments::where('transaction_id', $transction_id);
        $pembayaran_ke = $trans_payemnt->count();
        // $pembayaran_ke = $transaction->paid ? $pembayaran_ke : 1;
        // get latest payment id
        $payment_id = $trans_payemnt->latest()->first()->id;
        // get latest info of receipt saved
        $receipt = TransactionReceipts::where('transaction_payment_id', $payment_id)->latest()->first();
        $taken_date = ($receipt->issued_at != '1970-01-01 00:00:00') ? date('Y-m-d', strtotime($receipt->issued_at)) : '-';
        $taken_time = ($receipt->issued_at != '1970-01-01 00:00:00') ? date('H:i:00', strtotime($receipt->issued_at)) : '-';

        return view('invoices/print.blade.php', compact('transaction', 'det_trans', 'transaction_number', 'type', 'pembayaran_ke', 'taken_date', 'taken_time'));
    }

    public function reseller(Transactions $transaction)
    {
        // print_r($transaction);
        $transction_id = $transaction->id;
        $det_trans = TransactionDetails::query()
            ->leftJoin('products', 'product_id', '=', 'products.id')
            ->selectRaw("transaction_details.id, IF(product_id is null, concat_ws('',transaction_details.product_name, '\n', '<div class=\"tab\">', notes, '</div>'), concat_ws('',products.product_name, '\n', '<div class=\"tab\">', notes, '</div>')) as product_name, product_qty, product_price, product_subtotal, notes")
            ->where('transaction_id', $transction_id)->get();
        $transaction_number = LastTransactionNumber::where('transaction_id', $transction_id)->first()->id;
        // foreach($det_trans as $detail) {
        //     print_r($detail);
        // }
        $type = 'print';

        $trans_payemnt = TransactionPayments::where('transaction_id', $transction_id);
        $pembayaran_ke = $trans_payemnt->count();
        // $pembayaran_ke = $transaction->paid ? $pembayaran_ke : 1;
        // get latest payment id
        $payment_id = $trans_payemnt->latest()->first()->id;
        // get latest info of receipt saved
        $receipt = TransactionReceipts::where('transaction_payment_id', $payment_id)->latest()->first();
        $taken_date = ($receipt->issued_at != '1970-01-01 00:00:00') ? date('Y-m-d', strtotime($receipt->issued_at)) : '-';
        $taken_time = ($receipt->issued_at != '1970-01-01 00:00:00') ? date('H:i:00', strtotime($receipt->issued_at)) : '-';

        return view('invoices/print-reseller.blade.php', compact('transaction', 'det_trans', 'transaction_number', 'type', 'pembayaran_ke', 'taken_date', 'taken_time'));
    }

    public function download(Transactions $transaction)
    {
        // print_r($transaction);
        $transction_id = $transaction->id;
        $det_trans = TransactionDetails::query()
            ->leftJoin('products', 'product_id', '=', 'products.id')
            ->selectRaw("transaction_details.id, IF(product_id is null, concat_ws('',transaction_details.product_name, ' ', '<div class=\"tab\">', notes, '</div>'), concat_ws('',products.product_name, ' ', '<div class=\"tab\">', notes, '</div>')) as product_name, product_qty, product_price, product_subtotal, notes")
            ->where('transaction_id', $transction_id)->get();
        $transaction_number = LastTransactionNumber::where('transaction_id', $transction_id)->first()->id;
        // foreach($det_trans as $detail) {
        //     print_r($detail);
        // }
        $type = 'download';

        DB::beginTransaction();
        try {

            $trans_payemnt = TransactionPayments::where('transaction_id', $transction_id);
            $pembayaran_ke = $trans_payemnt->count();
            // $pembayaran_ke = $transaction->paid ? $pembayaran_ke : 1;
            // get latest payment id
            $payment_id = $trans_payemnt->latest()->first()->id;
                        // add struk tipe download copy from latest receipt
            // DB::insert("INSERT INTO transaction_receipts(issued_at,type, issued_by, created_at, transaction_payment_id) 
            //     SELECT issued_at, 2 as type, " . Auth::user()->id . ", now() as created_at,
            //     transaction_payment_id 
            //     FROM transaction_receipts WHERE transaction_payment_id = $payment_id ORDER BY created_at LIMIT 1");


            // get latest info of receipt saved
            $receipt = TransactionReceipts::where('transaction_payment_id', $payment_id)->latest()->first();
            $taken_date = ($receipt->issued_at != '1970-01-01 00:00:00') ? date('Y-m-d', strtotime($receipt->issued_at)) : '-';
            $taken_time = ($receipt->issued_at != '1970-01-01 00:00:00') ? date('H:i:00', strtotime($receipt->issued_at)) : '-';
        } catch (Exception $e) {
        }
        $pdf = Pdf::loadView('invoices/print.blade.php', compact('transaction', 'det_trans', 'transaction_number', 'type', 'pembayaran_ke', 'taken_date', 'taken_time'));

        return $pdf->download('invoice-'.$transaction->customer->name.'-'.$transaction->transaction_number.'.pdf');
    }

    public function downloadReseller(Transactions $transaction)
    {
        // print_r($transaction);
        $transction_id = $transaction->id;
        $det_trans = TransactionDetails::query()
            ->leftJoin('products', 'product_id', '=', 'products.id')
            ->selectRaw("transaction_details.id, IF(product_id is null, concat_ws('',transaction_details.product_name, ' ', '<div class=\"tab\">', notes, '</div>'), concat_ws('',products.product_name, ' ', '<div class=\"tab\">', notes, '</div>')) as product_name, product_qty, product_price, product_subtotal, notes")
            ->where('transaction_id', $transction_id)->get();
        $transaction_number = LastTransactionNumber::where('transaction_id', $transction_id)->first()->id;
        // foreach($det_trans as $detail) {
        //     print_r($detail);
        // }
        $type = 'download';
        $trans_payemnt = TransactionPayments::where('transaction_id', $transction_id);
        $pembayaran_ke = $trans_payemnt->count();
        // $pembayaran_ke = $transaction->paid ? $pembayaran_ke : 1;
        // get latest payment id
        $payment_id = $trans_payemnt->latest()->first()->id;
        // get latest info of receipt saved
        $receipt = TransactionReceipts::where('transaction_payment_id', $payment_id)->latest()->first();
        $taken_date = ($receipt->issued_at != '1970-01-01 00:00:00') ? date('Y-m-d', strtotime($receipt->issued_at)) : '-';
        $taken_time = ($receipt->issued_at != '1970-01-01 00:00:00') ? date('H:i:00', strtotime($receipt->issued_at)) : '-';

        $pdf = Pdf::loadView('invoices/print-reseller.blade.php', compact('transaction', 'det_trans', 'transaction_number', 'type', 'pembayaran_ke', 'taken_date', 'taken_time'));

        return $pdf->download('invoice-'.$transaction->reseller->name.'-'.$transaction->transaction_number.'.pdf');
    }
}
