<?php

namespace App\Http\Controllers\Report;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class ExportReportController extends Controller
{
    //
    public function sales()
    {
        return view("reports.sales");
    }

    public function receivables()
    {
        return view("reports.receivables");
    }
}
