<?php

namespace App\Imports;

use App\Models\Product;
use Maatwebsite\Excel\Concerns\ToModel;

class ProductImportTypeBahanLainnya implements ToModel
{
    /**
     * @param array $row
     *
     * @return \Illuminate\Database\Eloquent\Model|null
     */
    public function model(array $row)
    {
        info($row);
        if ($row[0] != "" && $row[0] != "No") {

            if ($row[1] != null) {
                return new Product([
                    //
                    'product_name' => $row[1],
                    'buy_price' => 0,
                    'customer_price' => $row[2],
                    'reseller_price' => 0,
                    'product_type_id' => 4,
                    'created_at' => date('Y-m-d H:i:s'),
                ]);
            }
        }
    }
}
