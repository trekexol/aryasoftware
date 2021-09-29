<?php

namespace App\Http\Controllers;

use App\Account;
use App\Branch;
use App\Company;
use App\DetailVoucher;
use App\HeaderVoucher;
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
        $datenow = $date->format('Y');
        $year_anterior   = date("Y",strtotime($datenow."- 1 year"));


        return view('admin.taxes.iva_index_payment',compact('datenow','year_anterior'));
 
    }

    public function iva_payment($month,$year)
    {

        $mes = $month;

        if($mes == 0){
            return redirect('/taxes/ivapaymentindex')->withDanger('Debe seleccionar un mes!');
        } 

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

        $account_iva = Account::on(Auth::user()->database_name)->where('code_one', 2)
        ->where('code_two', 1)
        ->where('code_three', 3)
        ->where('code_four', 1)
        ->where('code_five',4)
        ->first();

       

        $datenow        = Carbon::now();
        $ano            = $year;
        $dia            = $datenow->format('d');

        $date_begin     = Carbon::parse($ano."-".$nro_mes."-"."1");
        $date_end       = new Carbon($date_begin); 
        $date_end       = $date_end->endOfMonth()->format('Y-m-d');
        $date_begin     = $date_begin->format('Y-m-d');

       /* $mes_anterior   = date("Y-m-d",strtotime($fecha."- 1 month"));
        $date_begin     = $fecha ;
        $date_end       = $mes_anterior;*/

        $date_begin_last_month   = date("Y-m-d",strtotime($date_begin."- 1 month"));
        $date_end_last_month     = new Carbon($date_begin_last_month); 
        $date_end_last_month     = $date_end_last_month->endOfMonth()->format('Y-m-d');
        //Calculo de superavit
        $coin = 'bolivares';

        $debito_fical = Account::on(Auth::user()->database_name)->where('code_one', 2)
            ->where('code_two', 1)
            ->where('code_three', 3)
            ->where('code_four', 1)
            ->where('code_five',4)
            ->first();

        $debito_fiscal_total = $this->calculation_for_account($debito_fical,$coin,$date_begin,$date_end);

        $debito_fiscal_total_last_month = $this->calculation_for_account($debito_fical,$coin,$date_begin_last_month,$date_end_last_month);

        if($debito_fiscal_total < 0){
            $debito_fiscal_total = $debito_fiscal_total * -1;
        }

        if($debito_fiscal_total_last_month < 0){
            $debito_fiscal_total_last_month = $debito_fiscal_total_last_month * -1;
        }

        $iva_credito_fiscal = Account::on(Auth::user()->database_name)->where('code_one', 1)
            ->where('code_two', 1)
            ->where('code_three', 4)
            ->where('code_four', 1)
            ->where('code_five',1)
            ->first();

        $iva_credito_fiscal_total = $this->calculation_for_account($iva_credito_fiscal,$coin,$date_begin,$date_end); 

        $iva_credito_fiscal_total_last_month = $this->calculation_for_account($iva_credito_fiscal,$coin,$date_begin_last_month,$date_end_last_month); 

        if($iva_credito_fiscal_total < 0){
            $iva_credito_fiscal_total = $iva_credito_fiscal_total * -1;
        }

        if($iva_credito_fiscal_total_last_month < 0){
            $iva_credito_fiscal_total_last_month = $iva_credito_fiscal_total_last_month * -1;
        }



        $iva_retenido_tercero = Account::on(Auth::user()->database_name)->where('code_one', 1)
            ->where('code_two', 1)
            ->where('code_three', 4)
            ->where('code_four', 1)
            ->where('code_five',2)
            ->first();

        $iva_retenido_terceros_total = $this->calculation_for_account($iva_retenido_tercero,$coin,$date_begin,$date_end);

        
        if($iva_retenido_terceros_total < 0){
            $iva_retenido_terceros_total = $iva_retenido_terceros_total * -1;
        }


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

       
       $total_excedente = $debito_fiscal_total_last_month - $iva_credito_fiscal_total_last_month;

       
       if($total_excedente < 0){
            $total_excedente = $total_excedente * -1;
       }else{
            $total_excedente = 0;
       }

       $total_pay = $debito_fiscal_total - $iva_credito_fiscal_total - $total_excedente - $iva_retenido_terceros_total;

        if($total_pay < 0){
            $total_pay = 0;
        }

       return view('admin.taxes.iva_payment',compact('total_pay','iva_retenido_terceros_total','total_excedente','debito_fiscal_total','iva_credito_fiscal_total','providers','branches','account_iva','bcv','datenow','nro_mes','mes_nombre'));
   }

   /**
    * Store a newly created resource in storage.
    *
    * @param  \Illuminate\Http\Request  $request
    * @return \Illuminate\Http\Response
    */
   public function store(Request $request)
    {
        
        $data = request()->validate([
            'rate'  =>'required',
            'nro_mes'  =>'required',
            'Fecha_Year'  =>'required',
            'amount'  =>'required',
            'Filtro'  =>'required'

        ]);
        $user       =   auth()->user();
        $month = request('nro_mes');
        $year = request('Fecha_Year');

        $rate = str_replace(',', '.', str_replace('.', '',request('rate')));
        $total_pay = str_replace(',', '.', str_replace('.', '',request('total_pay')));
        $amount = str_replace(',', '.', str_replace('.', '',request('amount')));

        if($total_pay == 0){
            return redirect('/taxes/ivapayment/'.$month.'/'.$year)->withDanger('No tiene deuda por pagar!');
        } 
        if($amount == 0){
            return redirect('/taxes/ivapayment/'.$month.'/'.$year)->withDanger('El monto a pagar debe ser distinto de cero!');
        } 
        if($amount > $total_pay){
            return redirect('/taxes/ivapayment/'.$month.'/'.$year)->withDanger('El monto a pagar no puede ser mayor al monto total del Pago!');
        } 


        $datenow        = Carbon::now();

        $header_voucher  = new HeaderVoucher();
        $header_voucher->setConnection(Auth::user()->database_name);
        
        $header_voucher->description = "Pago Iva Debito Fiscal ".$month."/".$year." ".request('description');
        $header_voucher->date = $datenow;
        $header_voucher->reference = request('Nro_Ref');
        
        $header_voucher->status =  "1";
    
        $header_voucher->save();

        
        
        $account_iva = Account::on(Auth::user()->database_name)->where('code_one', 2)
        ->where('code_two', 1)
        ->where('code_three', 3)
        ->where('code_four', 1)
        ->where('code_five',4)
        ->first();

        $id_counterpart = request('Filtro');
        $account_counterpart = Account::on(Auth::user()->database_name)->find($id_counterpart);


        $this->add_movement($rate,$header_voucher->id,$account_iva->id,$user->id,$amount,0);
        
        $this->add_movement($rate,$header_voucher->id,$account_counterpart->id,$user->id,0,$amount);

        return redirect('/taxes/ivapayment/'.$month.'/'.$year)->withSuccess('Pago Exitoso!');
    }


    public function calculation_for_account($var,$coin,$date_begin,$date_end){

            $total_pago = 0;
            if($var->code_one == 2 && $var->code_two == 1 && $var->code_three == 3 && $var->code_four == 1 && $var->code_five == 4){ 
                //Si la cuenta es debito fiscal, procedemos a buscar los movimientos de pago si existen.
                $total_pago_debe =   DB::connection(Auth::user()->database_name)->select('SELECT SUM(d.debe) AS debe
                            FROM accounts a
                            INNER JOIN detail_vouchers d
                                ON d.id_account = a.id
                            INNER JOIN header_vouchers h
                                ON h.id = d.id_header_voucher
                            WHERE a.code_one = ? AND
                            a.code_two = ? AND
                            a.code_three = ? AND
                            a.code_four = ? AND
                            a.code_five = ? AND
                            d.status = ? AND
                            h.description LIKE ?
                            '
                        , [$var->code_one,$var->code_two,$var->code_three,$var->code_four,$var->code_five,'C',"Pago Iva Debito Fiscal ".date('m', strtotime($date_begin)) ."/".date('Y', strtotime($date_begin))."%"]);
                //dd("%Pago Iva Debito Fiscal ".date('m', strtotime($date_begin)) ."/".date('Y', strtotime($date_begin)));
                $total_pago = $total_pago_debe[0]->debe;
            }
            /*CALCULA LOS SALDOS DESDE DETALLE COMPROBANTE */
            if($coin == 'bolivares'){
                $total_debe =   DB::connection(Auth::user()->database_name)->select('SELECT SUM(d.debe) AS debe
                                                    FROM accounts a
                                            INNER JOIN detail_vouchers d
                                                ON d.id_account = a.id
                                            INNER JOIN header_vouchers h
                                                ON h.id = d.id_header_voucher
                                            WHERE a.code_one = ? AND
                                            a.code_two = ? AND
                                            a.code_three = ? AND
                                            a.code_four = ? AND
                                            a.code_five = ? AND
                                            d.status = ? AND
                                            (DATE_FORMAT(h.date, "%Y-%m-%d") >= ? AND DATE_FORMAT(h.date, "%Y-%m-%d") <= ?)
                                            '
                    , [$var->code_one,$var->code_two,$var->code_three,$var->code_four,$var->code_five,'C',$date_begin, $date_end]);
                $total_haber =   DB::connection(Auth::user()->database_name)->select('SELECT SUM(d.haber) AS haber
                                                        FROM accounts a
                                            INNER JOIN detail_vouchers d
                                                ON d.id_account = a.id
                                            INNER JOIN header_vouchers h
                                                ON h.id = d.id_header_voucher
                                            WHERE a.code_one = ? AND
                                            a.code_two = ? AND
                                            a.code_three = ? AND
                                            a.code_four = ? AND
                                            a.code_five = ? AND
                                            d.status = ? AND
                                            (DATE_FORMAT(h.date, "%Y-%m-%d") >= ? AND DATE_FORMAT(h.date, "%Y-%m-%d") <= ?)
                                            '
                    , [$var->code_one,$var->code_two,$var->code_three,$var->code_four,$var->code_five,'C',$date_begin, $date_end]);

                

                $total_dolar_debe =   DB::connection(Auth::user()->database_name)->select('SELECT SUM(d.debe/d.tasa) AS dolar
                                                        FROM accounts a
                                            INNER JOIN detail_vouchers d
                                                ON d.id_account = a.id
                                            INNER JOIN header_vouchers h
                                                ON h.id = d.id_header_voucher
                                            WHERE a.code_one = ? AND
                                            a.code_two = ? AND
                                            a.code_three = ? AND
                                            a.code_four = ? AND
                                            a.code_five = ? AND
                                            d.status = ? AND
                                            (DATE_FORMAT(h.date, "%Y-%m-%d") >= ? AND DATE_FORMAT(h.date, "%Y-%m-%d") <= ?)
                                            '
                    , [$var->code_one,$var->code_two,$var->code_three,$var->code_four,$var->code_five,'C',$date_begin, $date_end]);

                $total_dolar_haber =   DB::connection(Auth::user()->database_name)->select('SELECT SUM(d.haber/d.tasa) AS dolar
                                                        FROM accounts a
                                            INNER JOIN detail_vouchers d
                                                ON d.id_account = a.id
                                            INNER JOIN header_vouchers h
                                                ON h.id = d.id_header_voucher
                                            WHERE a.code_one = ? AND
                                            a.code_two = ? AND
                                            a.code_three = ? AND
                                            a.code_four = ? AND
                                            a.code_five = ? AND
                                            d.status = ? AND
                                            (DATE_FORMAT(h.date, "%Y-%m-%d") >= ? AND DATE_FORMAT(h.date, "%Y-%m-%d") <= ?)
                                            '
                    , [$var->code_one,$var->code_two,$var->code_three,$var->code_four,$var->code_five,'C',$date_begin, $date_end]);

                $var->balance =  $var->balance_previus;


            }else{
                $total_debe =   DB::connection(Auth::user()->database_name)->select('SELECT SUM(d.debe/d.tasa) AS debe
                                        FROM accounts a
                                            INNER JOIN detail_vouchers d
                                                ON d.id_account = a.id
                                            INNER JOIN header_vouchers h
                                                ON h.id = d.id_header_voucher
                                            WHERE a.code_one = ? AND
                                            a.code_two = ? AND
                                            a.code_three = ? AND
                                            a.code_four = ? AND
                                            a.code_five = ? AND
                                            d.status = ? AND
                                            (DATE_FORMAT(h.date, "%Y-%m-%d") >= ? AND DATE_FORMAT(h.date, "%Y-%m-%d") <= ?)
                                            '

                    , [$var->code_one,$var->code_two,$var->code_three,$var->code_four,$var->code_five,'C',$date_begin, $date_end]);

                $total_haber =   DB::connection(Auth::user()->database_name)->select('SELECT SUM(d.haber/d.tasa) AS haber
                                            FROM accounts a
                                            INNER JOIN detail_vouchers d
                                                ON d.id_account = a.id
                                            INNER JOIN header_vouchers h
                                                ON h.id = d.id_header_voucher
                                            WHERE a.code_one = ? AND
                                            a.code_two = ? AND
                                            a.code_three = ? AND
                                            a.code_four = ? AND
                                            a.code_five = ? AND
                                            d.status = ? AND
                                            (DATE_FORMAT(h.date, "%Y-%m-%d") >= ? AND DATE_FORMAT(h.date, "%Y-%m-%d") <= ?)
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

        $total_resultado =($total_debe - $total_haber) + $var->balance_previus + $total_pago;

        return $total_resultado;

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

    public function add_movement($bcv,$id_header,$id_account,$id_user,$debe,$haber){

        $detail = new DetailVoucher();
        $detail->setConnection(Auth::user()->database_name);


        $detail->id_account = $id_account;
        $detail->id_header_voucher = $id_header;
        $detail->user_id = $id_user;
        $detail->tasa = $bcv;

     
        $detail->debe = $debe;
        $detail->haber = $haber;
       
      
        $detail->status =  "C";

         /*Le cambiamos el status a la cuenta a M, para saber que tiene Movimientos en detailVoucher */
         
            $account = Account::on(Auth::user()->database_name)->findOrFail($detail->id_account);

            if($account->status != "M"){
                $account->status = "M";
                $account->save();
            }
         
    
        $detail->save();

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
