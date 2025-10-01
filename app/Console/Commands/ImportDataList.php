<?php

namespace App\Console\Commands;

use App\Imports\ProductImportTypeList;
use Illuminate\Console\Command;
use Maatwebsite\Excel\Facades\Excel;

class ImportDataList extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:import-data-list';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        //
        Excel::import(new ProductImportTypeList, base_path('list.xlsx'));
        
    }
}
