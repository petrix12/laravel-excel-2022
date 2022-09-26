<?php

namespace App\Imports;

use App\Models\Invoice;
use Carbon\Carbon;
// use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\ToModel;
// use Maatwebsite\Excel\Concerns\WithCustomCsvSettings;
// use Maatwebsite\Excel\Concerns\WithGroupedHeadingRow;
use PhpOffice\PhpSpreadsheet\Shared\Date;

class InvoiceImport implements ToModel /* ToCollection, WithGroupedHeadingRow, WithCustomCsvSettings */
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
            'user_id' => 1,
            'created_at' => Carbon::instance(Date::excelToDateTimeObject($row[4]))
            //'created_at' => Carbon::createFromFormat('d/m/Y', $row[4])
        ]);
    }
    /* public function collection($rows)
    {
        foreach($rows as $row){
            Invoice::create([
                'serie' => $row[0],
                'base' => $row[1],
                'igv' => $row[2],
                'total' => $row[3],
                'user_id' => 1
            ]);
        }
    } */

    // Definir delimitador y juego de caracteres de los archivos csv
    /* public function getCsvSettings(): array
    {
        return [
            'input_encoding' => 'UTF-8',
            'delimiter' => ';'
        ];
    } */
}
