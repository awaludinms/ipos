<?php

namespace App\Http\Controllers\Invoice;

use App\Http\Controllers\Controller;
use App\Models\LastTransactionNumber;
use App\Models\TransactionDetails;
use App\Models\Transactions;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;

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
        $transaction_number = LastTransactionNumber::all()->count();
        // foreach($det_trans as $detail) {
        //     print_r($detail);
        // }
        $type="print";
        
        return view("invoices/print.blade.php", compact('transaction', 'det_trans', 'transaction_number', 'type'));
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
        $type="print";

        return view("invoices/print-reseller.blade.php", compact('transaction', 'det_trans', 'transaction_number', 'type'));
    }

    public function download(Transactions $transaction)
    {
        // print_r($transaction);
        $transction_id = $transaction->id;
        $det_trans = TransactionDetails::query()
            ->leftJoin('products', 'product_id', '=', 'products.id')
            ->selectRaw("transaction_details.id, IF(product_id is null, concat_ws('',transaction_details.product_name, ' ', '<div class=\"tab\">', notes, '</div>'), concat_ws('',products.product_name, ' ', '<div class=\"tab\">', notes, '</div>')) as product_name, product_qty, product_price, product_subtotal, notes")
            ->where('transaction_id', $transction_id)->get();
        $transaction_number = LastTransactionNumber::all()->count();
        // foreach($det_trans as $detail) {
        //     print_r($detail);
        // }
        $type="download";

        $pdf = Pdf::loadView("invoices/print.blade.php", compact('transaction', 'det_trans', 'transaction_number', 'type'));
        return $pdf->download('invoice-' . $transaction->customer->name . '-' . $transaction->transaction_number . '.pdf');
    }

    public function downloadReseller(Transactions $transaction)
    {
        // print_r($transaction);
        $transction_id = $transaction->id;
        $det_trans = TransactionDetails::query()
            ->leftJoin('products', 'product_id', '=', 'products.id')
            ->selectRaw("transaction_details.id, IF(product_id is null, concat_ws('',transaction_details.product_name, ' ', '<div class=\"tab\">', notes, '</div>'), concat_ws('',products.product_name, ' ', '<div class=\"tab\">', notes, '</div>')) as product_name, product_qty, product_price, product_subtotal, notes")
            ->where('transaction_id', $transction_id)->get();
        $transaction_number = LastTransactionNumber::all()->count();
        // foreach($det_trans as $detail) {
        //     print_r($detail);
        // }
        $type="download";

        $pdf = Pdf::loadView("invoices/print-reseller.blade.php", compact('transaction', 'det_trans', 'transaction_number', 'type'));
        return $pdf->download('invoice-' . $transaction->customer->name . '-' . $transaction->transaction_number . '.pdf');
    }
}
