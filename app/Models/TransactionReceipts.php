<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Casts\Attribute;

class TransactionReceipts extends Model
{
    //
    protected $fillable = ['transaction_payment_id', 'issued_by', 'issued_at', 'type'];

    public function payment()
    {
        return $this->belongsTo(TransactionPayments::class, 'transaction_payment_id');
    }

    public function staff()
    {
        return $this->belongsTo(User::class, 'issued_by');
    }

    protected function TakenDate(): Attribute
    {
        return Attribute::make(
            get: fn ($value) => Carbon::parse($value)->isoFormat('dddd, D MMMM Y'),
        );
    }

    protected function TakenTime(): Attribute
    {
        return Attribute::make(
            get: fn ($value) => Carbon::parse($value)->isoFormat('HH:mm'),
        );
    }

}
