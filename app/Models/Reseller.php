<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Reseller extends Model
{
    //
    protected $table = "reseller";

    protected $fillable = ['name', 'phone', 'address'];

    public function transactions()
    {
        return $this->hasMany(Transactions::class)
            ->where('transactions.transaction_state', 3);
    }
}
