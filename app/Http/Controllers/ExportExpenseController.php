<?php

namespace App\Http\Controllers;

use App\Company;
use App\ExpensesAndPurchase;
use App\ExpensesDetail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\DB;

use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;

class ExportExpenseController extends Controller
{
    public function ivaTxt() 
   {
        $content = "";
        $total_retiene_iva = 0;
        $date = Carbon::now();
        $company = Company::on(Auth::user()->database_name)->first();
        $expenses = ExpensesAndPurchase::on(Auth::user()->database_name)->where('retencion_iva','<>',0)->get();
        if(isset($expenses)){
            foreach ($expenses as  $expense) {
                $expense->date = Carbon::parse($expense->date);
                $total_retiene_iva = $this->calculatarTotalProductosSinIva($expense);
                
                $content .= $company->code_rif.'	'.$expense->date->format('Ym').'  '.$expense->date->format('Y-m-d').'	C	01	'
                .$expense->providers['code_provider'].'	'.$expense->invoice.'	'.$expense->serie.'	 '.$expense->amount_with_iva.'	 '.$expense->base_imponible
                .'	 '.$expense->retencion_iva.'	 0'.$expense->id.'  '.$total_retiene_iva.'	 '.$expense->iva_percentage.'    0';
                $content .= "\n";
            }    
        }else{
            $content = "No hay Compras con Retencion de IVA";
        }
        
        // file name to download
        $fileName = "retencion-de-iva-provedores.txt";

      
        // make a response, with the content, a 200 response code and the headers
        return Response::make($content, 200, [
        'Content-type' => 'text/plain', 
        'Content-Disposition' => sprintf('attachment; filename="%s"', $fileName),
        'Content-Length' => strlen($content)]);
   }


   public function calculatarTotalProductosSinIva($expense)
   {
        $request =  ExpensesDetail::on(Auth::user()->database_name)
                        ->where('id_expense',$expense->id)
                        ->where('exento','1')
                        ->select(DB::raw('SUM(price*amount) As total'))
                        ->first();

        return bcdiv($request->total, '1', 2);
   }
}
