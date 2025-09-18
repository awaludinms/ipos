<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TransactionDetails extends Model
{
    protected $fillable = [
        'product_id', 'product_price', 'product_qty', 'transaction_id',
        'created_by', 'product_subtotal', 'product_name', 'notes'
    ];
    //
    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}
