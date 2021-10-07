<?php

namespace App\Http\Controllers;

use App\Exports\ExpensesExport;
use App\Imports\ExpensesImport;
use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\Collection;
use Maatwebsite\Excel\Facades\Excel;

class ExcelController extends Controller
{
    public function export($id) 
   {
       
       $export = new ExpensesExport([
            ['id_compra', 'id_inventario (NULL)', 'id_cuenta','id_sucursal (NULL)','descripcion','exento','islr','cantidad','precio','tasa(BCV)'],
            [$id]
       ]);

       return Excel::download($export, 'invs.xlsx');
   }

   public function import(Request $request) 
   {
       dd($request);
       Excel::import(new ExpensesImport, 'users.xlsx');
       
       return redirect('/')->with('success', 'All good!');
   }
}
