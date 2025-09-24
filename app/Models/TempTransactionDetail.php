<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Casts\Attribute;


class TempTransactionDetail extends Model
{
    protected $fillable = [
        'product_id', 'product_price', 'product_qty', 'temp_transaction_id',
        'created_by', 'product_subtotal', 'product_name', 'notes'
    ];
    //
    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    protected function TransactionDateSimpleFormatted(): Attribute
    {
        return Attribute::make(
            get: fn ($value) => Carbon::parse($value)->isoFormat('D MMM Y HH:mm'),
        );
    }
}
