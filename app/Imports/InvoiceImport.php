<?php

namespace App\Imports;

use App\Models\Invoice;
use Maatwebsite\Excel\Concerns\ToModel;

class InvoiceImport implements ToModel
{
    /**
    * @param array $row
    *
    * @return \Illuminate\Database\Eloquent\Model|null
    */
    public function model(array $row)
    {
        return new Invoice([
            'serie' => $row[0],
            'base' => $row[1],
            'igv' => $row[2],
            'total' => $row[3],
            'user_id' => 1
        ]);
    }
}
