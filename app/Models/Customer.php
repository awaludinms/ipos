<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Customer extends Model
{
    //
    protected $table = "customer";

    protected $fillable = ['name', 'phone', 'address'];
}
