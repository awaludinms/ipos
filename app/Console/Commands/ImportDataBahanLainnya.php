<?php

namespace App\Console\Commands;

use App\Imports\ProductImportTypeBahanLainnya;
use Illuminate\Console\Command;
use Maatwebsite\Excel\Facades\Excel;

class ImportDataBahanLainnya extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:import-data-bahan-lainnya';

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
        Excel::import(new ProductImportTypeBahanLainnya, base_path('bahanlainnya.xlsx'));
        
    }
}
