<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    //
    public $fillable = ['product_name', 'buy_price', 'customer_price', 'reseller_price', 'product_type_id'];

    public function product_type()
    {
        return $this->belongsTo(ProductType::class);
    }

    protected function CustomerPriceFormatted(): Attribute
    {
        return Attribute::make(
            get: fn (string $value) => 'Rp. ' . number_format($value,0,'.')
        );
    }

    protected function ResellerPriceFormatted(): Attribute
    {
        return Attribute::make(
            get: fn (string $value) => 'Rp. '. number_format($value,0,'.')
        );
    }
}
