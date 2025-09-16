<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Transactions extends Model
{
    //
    protected $fillable = [
        'customer_id',
        'transaction_pay_type',
        'transaction_state',
        'change_return',
        'grand_total',
        'staff_id',
        'transaction_date'
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
}
