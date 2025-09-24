<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Casts\Attribute;


class TransactionPayments extends Model
{
    //
    protected $fillable = [
        'transaction_id',
        'amount',
        'method',
        'trans_status',
        'staff_id',
        'change_return'
    ];

    protected function TransactionPaymentDateFormatted(): Attribute
    {
        return Attribute::make(
            get: fn ($value) => Carbon::parse($value)->isoFormat('dddd, D MMMM Y HH:mm'),
        );
    }

    public function staff()
    {
        return $this->belongsTo(User::class, 'staff_id');
    }
}
