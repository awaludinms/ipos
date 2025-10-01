<?php

namespace App\Console\Commands;

use App\Imports\ProductImportTypeBahanSticker;
use Illuminate\Console\Command;
use Maatwebsite\Excel\Facades\Excel;

class ImportDataBahanSticker extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:import-data-bahan-sticker';

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
        Excel::import(new ProductImportTypeBahanSticker, base_path('bahansticker.xlsx'));
        
    }
}
