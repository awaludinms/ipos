<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Casts\Attribute;


class TempTransaction extends Model
{
    protected $fillable = [
        'customer_id',
        'transaction_pay_type',
        'transaction_state',
        'change_return',
        'grand_total',
        'staff_id',
        'transaction_date',
        'customer_type',
        'customer_id',
        'reseller_id',
        'paid',
        'transaction_number',
        'customer_name',
        'pay_status',
        'staff_name',
        'staff_id',
        'id',
    ];

    public function details()
    {
        return $this->hasMany(TransactionDetails::class);
    }

    public function customer()
    {
        return $this->belongsTo(Customer::class, 'customer_id');
    }

    public function reseller()
    {
        return $this->belongsTo(Reseller::class,'reseller_id');
    }

    public function staff()
    {
        return $this->belongsTo(User::class,'staff_id');
    }

    protected function TransactionDateFormatted(): Attribute
    {
        return Attribute::make(
            get: fn ($value) => Carbon::parse($value)->isoFormat('dddd, D MMMM Y'),
        );
    }

    public function transaction_note()
    {
        return $this->hasOne(LastTransactionNumber::class,'');
    }
}
