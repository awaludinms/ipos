<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Customer extends Model
{
    //
    protected $table = "customer";

    protected $fillable = ['name', 'phone', 'address'];

    public function transactions()
    {
        return $this->hasMany(Transactions::class)
            ->where('transactions.transaction_state', 3);
    }
}
