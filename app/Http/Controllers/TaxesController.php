<?php

namespace App\Http\Controllers;

use App\Account;
use App\Branch;
use App\Company;
use App\Provider;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class TaxesController extends Controller
{

    public function __construct(){

        $this->middleware('auth');
    }

    public function iva_paymentindex()
    {
        $date = Carbon::now();
        $datenow = $date->format('Y-m-d');
        return view('admin.taxes.iva_index_payment');
    }

    public function iva_payment(Request $request)
    {
        $mes= request('Filtro_Meses');

        if($mes == '01'){
            $nro_mes    = "01";
            $mes_nombre = "ENERO";
        }elseif($mes == '02'){
            $nro_mes    = "02";
            $mes_nombre = "FEBRERO";
        }elseif($mes == '03'){
            $nro_mes    = "03";
            $mes_nombre = "MARZO";
        }elseif($mes == '04'){
            $nro_mes    = "04";
            $mes_nombre = "ABRIL";
        }elseif($mes == '05'){
            $nro_mes    = "05";
            $mes_nombre = "MAYO";
        }elseif($mes == '06'){
            $nro_mes    = "06";
            $mes_nombre = "JUNIO";
        }elseif($mes == '07'){
            $nro_mes    = "07";
            $mes_nombre = "JULIO";
        }elseif($mes == '08'){
            $nro_mes    = "08";
            $mes_nombre = "AGOSTO";
        }elseif($mes == '09'){
            $nro_mes    = "09";
            $mes_nombre = "SEPTIEMBRE";
        }elseif ($mes == '10'){
            $nro_mes    = "10";
            $mes_nombre = "OCTUBRE";
        }elseif ($mes == '11'){
            $nro_mes    = "11";
            $mes_nombre = "NOVIEMBRE";
        }else {
            $nro_mes    = "12";
            $mes_nombre = "DICIEMBRE";
        }


        $datenow        = Carbon::now();
        $ano            = $datenow->format('Y');
        $dia            = $datenow->format('d');

        $date_begin     = Carbon::parse($ano."-".$nro_mes."-"."1");
        $date_end       = $date_begin->endOfMonth()->format('Y-m-d');



       /* $mes_anterior   = date("Y-m-d",strtotime($fecha."- 1 month"));
        $date_begin     = $fecha ;
        $date_end       = $mes_anterior;*/


        //Calculo de superavit
        $coin = 'bolivares';

        

        $var = Account::on(Auth::user()->database_name)->where('code_one', 2)
            ->where('code_two', 1)
            ->where('code_three', 3)
            ->where('code_four', 1)
            ->where('code_five',4)
            ->first();
            /*CALCULA LOS SALDOS DESDE DETALLE COMPROBANTE */
            if($coin == 'bolivares'){
                $total_debe =   DB::connection(Auth::user()->database_name)->select('SELECT SUM(d.debe) AS debe
                                                    FROM accounts a
                                            INNER JOIN detail_vouchers d
                                                ON d.id_account = a.id
                                            WHERE a.code_one = ? AND
                                            a.code_two = ? AND
                                            a.code_three = ? AND
                                            a.code_four = ? AND
                                            a.code_five = ? AND
                                            d.status = ? AND
                                            (DATE_FORMAT(d.created_at, "%Y %m %d") >= ? AND DATE_FORMAT(d.created_at, "%Y %m %d") <= ?)
                                            '
                    , [$var->code_one,$var->code_two,$var->code_three,$var->code_four,$var->code_five,'C',$date_begin, $date_end]);
                $total_haber =   DB::connection(Auth::user()->database_name)->select('SELECT SUM(d.haber) AS haber
                                                         FROM accounts a
                                            INNER JOIN detail_vouchers d
                                                ON d.id_account = a.id
                                            WHERE a.code_one = ? AND
                                            a.code_two = ? AND
                                            a.code_three = ? AND
                                            a.code_four = ? AND
                                            a.code_five = ? AND
                                            d.status = ?AND
                                            (DATE_FORMAT(d.created_at, "%Y %m %d") >= ? AND DATE_FORMAT(d.created_at, "%Y %m %d") <= ?)
                                            '
                    , [$var->code_one,$var->code_two,$var->code_three,$var->code_four,$var->code_five,'C',$date_begin, $date_end]);

                $total_dolar_debe =   DB::connection(Auth::user()->database_name)->select('SELECT SUM(d.debe/d.tasa) AS dolar
                                                        FROM accounts a
                                            INNER JOIN detail_vouchers d
                                                ON d.id_account = a.id
                                            WHERE a.code_one = ? AND
                                            a.code_two = ? AND
                                            a.code_three = ? AND
                                            a.code_four = ? AND
                                            a.code_five = ? AND
                                            d.status = ?
                                            '
                    , [$var->code_one,$var->code_two,$var->code_three,$var->code_four,$var->code_five,'C',$date_begin, $date_end]);

                $total_dolar_haber =   DB::connection(Auth::user()->database_name)->select('SELECT SUM(d.haber/d.tasa) AS dolar
                                                         FROM accounts a
                                            INNER JOIN detail_vouchers d
                                                ON d.id_account = a.id
                                            WHERE a.code_one = ? AND
                                            a.code_two = ? AND
                                            a.code_three = ? AND
                                            a.code_four = ? AND
                                            a.code_five = ? AND
                                            d.status = ? AND
                                            (DATE_FORMAT(d.created_at, "%Y %m %d") >= ? AND DATE_FORMAT(d.created_at, "%Y %m %d") <= ?)
                                            '
                    , [$var->code_one,$var->code_two,$var->code_three,$var->code_four,$var->code_five,'C',$date_begin, $date_end]);

                $var->balance =  $var->balance_previus;


            }else{
                $total_debe =   DB::connection(Auth::user()->database_name)->select('SELECT SUM(d.debe/d.tasa) AS debe
                                          FROM accounts a
                                            INNER JOIN detail_vouchers d
                                                ON d.id_account = a.id
                                            WHERE a.code_one = ? AND
                                            a.code_two = ? AND
                                            a.code_three = ? AND
                                            a.code_four = ? AND
                                            a.code_five = ? AND
                                            d.status = ?AND
                                            (DATE_FORMAT(d.created_at, "%Y %m %d") >= ? AND DATE_FORMAT(d.created_at, "%Y %m %d") <= ?)
                                            '

                    , [$var->code_one,$var->code_two,$var->code_three,$var->code_four,$var->code_five,'C',$date_begin, $date_end]);

                $total_haber =   DB::connection(Auth::user()->database_name)->select('SELECT SUM(d.haber/d.tasa) AS haber
                                            FROM accounts a
                                            INNER JOIN detail_vouchers d
                                                ON d.id_account = a.id
                                            WHERE a.code_one = ? AND
                                            a.code_two = ? AND
                                            a.code_three = ? AND
                                            a.code_four = ? AND
                                            a.code_five = ? AND
                                            d.status = ? AND
                                            (DATE_FORMAT(d.created_at, "%Y %m %d") >= ? AND DATE_FORMAT(d.created_at, "%Y %m %d") <= ?)
                                            '
                    , [$var->code_one,$var->code_two,$var->code_three,$var->code_four,$var->code_five,'C',$date_begin, $date_end]);
            }
            $total_debe     = $total_debe[0]->debe;
            $total_haber    = $total_haber[0]->haber;
            if(isset($total_dolar_debe[0]->dolar)){
                $total_dolar_debe   = $total_dolar_debe[0]->dolar;
                $var->dolar_debe    = $total_dolar_debe;
            }
            if(isset($total_dolar_haber[0]->dolar)){
                $total_dolar_haber  = $total_dolar_haber[0]->dolar;
                $var->dolar_haber   = $total_dolar_haber;
            }

            $var->debe      = $total_debe;
            $var->haber     = $total_haber;

            if(($var->balance_previus != 0) && ($var->rate !=0)){
                $var->balance =  $var->balance_previus;
            }

        $total_resultado =($total_debe - $total_haber)  - $var->balance_previus;

        $total_resultado = $total_resultado *-1;

        /*$total_debito = $total_resultado 0.16;

            dd($total_debito);
*/

        $debito_fical = Account::on(Auth::user()->database_name)->where('code_one', 2)
            ->where('code_two', 1)
            ->where('code_three', 3)
            ->where('code_four', 1)
            ->where('code_five',4)
            ->first();


        $iva_credito_fiscal = Account::on(Auth::user()->database_name)->where('code_one', 1)
            ->where('code_two', 1)
            ->where('code_three', 4)
            ->where('code_four', 1)
            ->where('code_five',1)
            ->first();

        $iva_retenido_tercero= Account::on(Auth::user()->database_name)->where('code_one', 1)
            ->where('code_two', 1)
            ->where('code_three', 4)
            ->where('code_four', 1)
            ->where('code_five',2)
            ->first();


        $account_iva = Account::on(Auth::user()->database_name)->where('code_one', 2)
            ->where('code_two', 1)
            ->where('code_three', 3)
            ->where('code_four', 1)
            ->where('code_five',4)
            ->first();




       $account_impuestos = Account::on(Auth::user()->database_name)->where('code_one', 1)
           ->where('code_two', 1)
           ->where('code_three', 4)
           ->where('code_four', 1)
           ->orderBY('description','asc')->pluck('description','id')->toArray();



       $providers = Provider::on(Auth::user()->database_name)->orderBy('id', 'asc')->get();

       $branches = Branch::on(Auth::user()->database_name)->orderBY('description','asc')->pluck('description','id')->toArray();

        /*Revisa si la tasa de la empresa es automatica o fija*/
        $company = Company::on(Auth::user()->database_name)->find(1);

       //Si la taza es automatica
       if($company->tiporate_id == 1){
           //esto es para que siempre se pueda guardar la tasa en la base de datos
           $bcv = $this->search_bcv();
       }else{
           //si la tasa es fija
           $bcv_quotation_product = $company->rate;
           $bcv = $company->rate;
       }




       return view('admin.taxes.iva_payment',compact('providers','branches','account_iva','bcv','datenow','account_impuestos','total_resultado','nro_mes','mes_nombre'));
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
            'razon_social'          =>'required|max:80',
            'direction'             =>'required|max:100',

            'city'                  =>'required|max:20',
            'country'               =>'required|max:20',
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
