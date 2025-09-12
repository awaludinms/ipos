<?php

namespace App\Models;

use Carbon\Carbon;
use DateTime;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;

class Expenses extends Model
{
    //
    protected $fillable = ['expenses_date', 'description', 'person', 'expense_value', 'updated_by', 'created_by', 'deleted_by', 'deleted_at'];

    protected function ExpensesDateFormatted(): Attribute
    {
        return Attribute::make(
            get: fn ($value) => Carbon::parse($value)->isoFormat('dddd, d MMMM Y'),
        );
    }

    protected function ExpenseValueFormatted(): Attribute
    {
        return Attribute::make(
            get: fn (string $value) => 'Rp. ' . number_format($value,0,'.')
        );
    }
}
