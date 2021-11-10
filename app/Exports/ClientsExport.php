<?php

namespace App\Exports;


use Maatwebsite\Excel\Concerns\FromCollection;

use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Collection;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Facades\Excel;

use Maatwebsite\Excel\Concerns\FromArray;

class ClientsExport implements FromArray
{
    /**
     * @return \Illuminate\Support\Collection
     */
    protected $clients;

    public function __construct(array $clients)
    {
        $this->clients = $clients;
    }

    public function array(): array
    {
        return $this->clients;
    }



}
