<?php

namespace App\Imports;

use App\Client;
use Carbon\Carbon;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class ClientsImport implements ToModel,WithHeadingRow
{
    /**
    * @param array $row
    *
    * @return \Illuminate\Database\Eloquent\Model|null
    */
    public function model(array $row)
    {
        $user       =   auth()->user();
        $date = Carbon::now();

        return new Client([

            'id_user'           => $user->id,
            'type_code'        => $row['type_code'],
            'cedula_rif'        => $row['cedula_rif'],
            'name'         => $row['name'],
            'direction'       => $row['direction'],
            'city'            => $row['city'],
            'country'              => $row['country'],
            'phone1'            => $row['phone1'],
            'days_credit'             => $row['days_credit'],
            'amount_max_credit'              => $row['amount_max_credit'],
            'percentage_retencion_iva'              => $row['percentage_retencion_iva'],
            'percentage_retencion_islr'              => $row['percentage_retencion_islr'],
            'status'            => 1,
            'created_at'        => $date,
            'updated_at'        => $date,
        ]);
    }
}
