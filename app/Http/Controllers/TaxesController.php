<?php

namespace App\Http\Controllers;

use App\Account;
use App\Branch;
use App\Company;
use App\Provider;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TaxesController extends Controller
{

    public function __construct(){

        $this->middleware('auth');
    }


    public function iva_payment()
   {
        
       
       $account_iva = Account::on(Auth::user()->database_name)->where('code_one', 2)
       ->where('code_two', 1)
       ->where('code_three', 3)
       ->where('code_four', 1)
       ->where('code_five',4)
       ->first();
       
       $providers = Provider::on(Auth::user()->database_name)->orderBy('id', 'asc')->get();

       $branches = Branch::on(Auth::user()->database_name)->orderBy('id', 'asc')->get();
      

        /*Revisa si la tasa de la empresa es automatica o fija*/
        $company = Company::on(Auth::user()->database_name)->find(1);
        //Si la taza es automatica
        if($company->tiporate_id == 1){
            $bcv = $this->search_bcv();
        }else{
            //si la tasa es fija
            $bcv = $company->rate;
        }

        $date = Carbon::now();
        $datenow = $date->format('Y-m-d');

       return view('admin.taxes.iva_payment',compact('providers','branches'
                                                    ,'account_iva','bcv','datenow'));
   }

  
   /**
    * Store a newly created resource in storage.
    *
    * @param  \Illuminate\Http\Request  $request
    * @return \Illuminate\Http\Response
    */
   public function store(Request $request)
    {
        dd($request);
        $data = request()->validate([
            'code_provider'         =>'required|max:20',
            'razon_social'         =>'required|max:80',
            'direction'         =>'required|max:100',

            'city'         =>'required|max:20',
            'country'         =>'required|max:20',
            'phone1'         =>'required|max:20',
            'phone2'         =>'required|max:20',

            
            'days_credit'           =>  'required|integer',
            'amount_max_credit'     =>  'required',
            'porc_retencion_iva'    =>  'numeric|min:0|max:100',
            'porc_retencion_islr'    => 'numeric|min:0|max:100',
            
            'balance'         =>'required',
            
        
        
        ]);
        
        $users = new Provider();
        $users->setConnection(Auth::user()->database_name);

        
        $users->code_provider = request('code_provider');
        $users->razon_social = request('razon_social');
        $users->direction = request('direction');
        $users->city = request('city');
        $users->country = request('country');
        $users->phone1 = request('phone1');
        $users->phone2 = request('phone2');

        $has_credit = request('has_credit');
        if($has_credit == null){
            $users->has_credit = false;
        }else{
            $users->has_credit = true;
        }
    
        $users->days_credit = request('days_credit');

        $sin_formato_amount_max_credit = str_replace(',', '.', str_replace('.', '', request('amount_max_credit')));
        $sin_formato_balance = str_replace(',', '.', str_replace('.', '', request('balance')));
    
        
        $users->amount_max_credit = $sin_formato_amount_max_credit;
        $users->porc_retencion_iva = request('porc_retencion_iva');
        $users->porc_retencion_islr = request('porc_retencion_islr');
        
        $users->balance = $sin_formato_balance;

        $users->status =  1;
    
        $users->save();

        return redirect('/taxes/ivapayment')->withSuccess('Registro Exitoso!');
    }




    public function search_bcv()
    {
        /*Buscar el indice bcv*/
        $urlToGet ='http://www.bcv.org.ve/tasas-informativas-sistema-bancario';
        $pageDocument = @file_get_contents($urlToGet);
        preg_match_all('|<div class="col-sm-4 col-xs-4 centrado"><strong> (.*?) </strong> </div>|s', $pageDocument, $cap);

        if ($cap[0] == array()){ // VALIDAR Concidencia
            $titulo = '0,00';
        }else {
            $titulo = $cap[1][8];
        }

        $bcv_con_formato = $titulo;
        $bcv = str_replace(',', '.', str_replace('.', '',$bcv_con_formato));


        /*-------------------------- */
        return $bcv;

    }


    public function list_account(Request $request, $type){
        //validar si la peticion es asincrona
        if($request->ajax()){
            try{
                
                if($type == "contado"){
                    $account = Account::on(Auth::user()->database_name)->select('id','description')->where('code_one', 1)
                    ->where('code_two', 1)
                    ->where('code_three', 1)
                    ->whereIn('code_four', [1,2])
                    ->where('code_five', '<>',0)
                    ->get();
                }else{
                    $account = Account::on(Auth::user()->database_name)->select('id','description')->where('code_one', 2)
                    ->where('code_two', 2)
                    ->where('code_three', 1)
                    ->where('code_four', 1)
                    ->where('code_five', '<>',0)
                    ->get();
                }
                

                return response()->json($account,200);
            }catch(Throwable $th){
                return response()->json(false,500);
            }
        }
        
    }
}
