<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Reseller extends Model
{
    //
    protected $table = "reseller";

    protected $fillable = ['name', 'phone', 'address'];
}
