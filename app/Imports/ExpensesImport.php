<?php

namespace App\Imports;

use App\ExpensesDetail;
use Maatwebsite\Excel\Concerns\ToModel;

class ExpensesImport implements ToModel
{
    /**
    * @param array $row
    *
    * @return \Illuminate\Database\Eloquent\Model|null
    */
    public function model(array $row)
    {
        return new ExpensesDetail([
            'name'     => $row[0],
            'email'    => $row[1], 
            'password' => Hash::make($row[2]),
        ]);
    }
}
