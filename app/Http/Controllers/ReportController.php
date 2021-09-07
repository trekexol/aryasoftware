<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use Illuminate\Http\Request;


use App;
use App\Account;
use App\Client;
use App\Company;
use App\DetailVoucher;
use App\Provider;
use App\Quotation;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;


class ReportController extends Controller
{
    public function index()
    {
        
        $user       =   auth()->user();
        $users_role =   $user->role_id;
        if($users_role == '1'){
            $date = Carbon::now();
            $datenow = $date->format('Y-m-d');    
            $detail_old = DetailVoucher::on(Auth::user()->database_name)->orderBy('created_at','asc')->first();
            $date_begin = $detail_old;
            $datebeginyear = $date->firstOfYear()->format('Y-m-d');

        }elseif($users_role == '2'){
            return view('admin.index');
        }

        
    
        return view('admin.reports.index_balance_general',compact('datebeginyear','datenow','detail_old','date_begin'));
      
    }

    public function index_ingresos()
    {
        
        $user       =   auth()->user();
        $users_role =   $user->role_id;
        if($users_role == '1'){
            $date = Carbon::now();
            $datenow = $date->format('Y-m-d');    
            $detail_old = DetailVoucher::on(Auth::user()->database_name)->orderBy('created_at','asc')->first();
            $datebeginyear = $date->firstOfYear()->format('Y-m-d');

        }elseif($users_role == '2'){
            return view('admin.index');
        }

        return view('admin.reports.index_ingresos_egresos',compact('datebeginyear','datenow','detail_old'));
      
    }
    
    public function index_accounts_receivable($id_client = null)
    {
        
        $user       =   auth()->user();
        $users_role =   $user->role_id;
        if($users_role == '1'){
            $date = Carbon::now();
            $datenow = $date->format('Y-m-d');   
            $client = null; 

            if(isset($id_client)){
                $client    = Client::on(Auth::user()->database_name)->find($id_client);
            }
            

        }elseif($users_role == '2'){
            return view('admin.index');
        }

        return view('admin.reports.index_accounts_receivable',compact('client','datenow'));
      
    }

    public function index_debtstopay($id_provider = null)
    {
        
        $user       =   auth()->user();
        $users_role =   $user->role_id;
        if($users_role == '1'){
            $date = Carbon::now();
            $datenow = $date->format('Y-m-d');   
            $provider = null; 

            if(isset($id_provider)){
                $provider    = Provider::on(Auth::user()->database_name)->find($id_provider);
            }
            

        }elseif($users_role == '2'){
            return view('admin.index');
        }

        return view('admin.reports.index_debtstopay',compact('provider','datenow'));
      
    }


    public function index_ledger()
    {
        
        $user       =   auth()->user();
        $users_role =   $user->role_id;
       
            $date = Carbon::now();
            $datenow = $date->format('Y-m-d');    
            
            $datebeginyear = $date->firstOfYear()->format('Y-m-d');

        return view('admin.reports.index_ledger',compact('datebeginyear','datenow'));
      
    }

    public function store(Request $request)
    {
        
        $date_begin = request('date_begin');
        $date_end = request('date_end');
        $level = request('level');
        $coin = request('coin');
        
        
        return view('admin.reports.index_balance_general',compact('date_begin','date_end','level','coin'));
    }

    public function store_ingresos(Request $request)
    {
        
        $date_begin = request('date_begin');
        $date_end = request('date_end');
        $level = request('level');
        $coin = request('coin');
        
        
        return view('admin.reports.index_ingresos_egresos',compact('date_begin','date_end','level','coin'));
    }


    public function store_accounts_receivable(Request $request)
    {
        
        $date_end = request('date_end');
        $type = request('type');
        $id_client = request('id_client');
        $typeinvoice = request('typeinvoice');

        $client = null;

        if($type != 'todo'){

            if(isset($id_client)){
                $client    = Client::on(Auth::user()->database_name)->find($id_client);
            }
        }

        return view('admin.reports.index_accounts_receivable',compact('typeinvoice','date_end','client'));
    }

    public function store_debtstopay(Request $request)
    {
        
        $date_end = request('date_end');
        $type = request('type');
        $id_provider = request('id_provider');

        $provider = null;

        if($type != 'todos'){

            if(isset($id_provider)){
                $provider    = Provider::on(Auth::user()->database_name)->find($id_provider);
            }
        }

        return view('admin.reports.index_debtstopay',compact('date_end','provider'));
    }

    function balance_pdf($coin = null,$date_begin = null,$date_end = null,$level = null)
    {
      
        $pdf = App::make('dompdf.wrapper');

        
        $date = Carbon::now();
        $datenow = $date->format('Y-m-d'); 
        $period = $date->format('Y'); 
        $detail_old = DetailVoucher::on(Auth::user()->database_name)->orderBy('created_at','asc')->first();
        
         


        if(isset($date_begin)){
            $from = $date_begin;
        }else{
            $from = $detail_old->created_at->format('Y-m-d');
            
        }
        if(isset($date_end)){
            $to = $date_end;
        }else{
            $to = $datenow;
        }
        if(isset($level)){
            
        }else{
            $level = 5;
        }

        if(isset($coin) && ($coin == "bolivares")){
            $accounts_all = $this->calculation($from,$to);
        }else{
            $accounts_all = $this->calculation_dolar("dolares");
        }
      

        $accounts = $accounts_all->filter(function($account)
        {
            if($account->code_one <= 3){
                $total = $account->balance_previus + $account->debe - $account->haber;
                if ($total != 0) {
                    return $account;
                }
            }
            
        });

        /*$foto = auth()->user()->company->foto_company;
        $code_rif = auth()->user()->company->code_rif;*/

        
        $pdf = $pdf->loadView('admin.reports.balance_general',compact('coin','datenow','accounts','level','detail_old','date_begin','date_end'));
        return $pdf->stream();
                 
    }
    
    function accounts_receivable_pdf($date_end,$typeinvoice,$id_client = null)
    {
        
        $pdf = App::make('dompdf.wrapper');
        $quotations = null;
        
        $date = Carbon::now();
        $datenow = $date->format('d-m-Y'); 
        if(empty($date_end)){
            $date_end = $datenow;

            $date_consult = $date->format('Y-m-d'); 
        }else{
            $date_end = Carbon::parse($date_end)->format('d-m-Y');

            $date_consult = Carbon::parse($date_end)->format('Y-m-d');
        }
        
        $period = $date->format('Y'); 
        
      
        if(isset($id_client)){
            if(isset($typeinvoice) && ($typeinvoice == 'notas')){
                $quotations = DB::connection(Auth::user()->database_name)->table('quotations')
                ->join('clients', 'clients.id','=','quotations.id_client')
                ->leftjoin('anticipos', 'anticipos.id_quotation','=','quotations.id')
                ->whereIn('quotations.status',[1,'P'])
                ->where('quotations.amount','<>',null)
                ->where('quotations.date_quotation','<=',$date_consult)
                ->where('quotations.date_delivery_note','<>',null)
                ->where('quotations.id_client',$id_client)
                ->select('quotations.date_quotation','quotations.id','quotations.serie','clients.name as name_client','quotations.amount','quotations.amount_with_iva', DB::raw('SUM(anticipos.amount) As amount_anticipo'))
                ->groupBy('quotations.date_quotation','quotations.id','quotations.serie','clients.name','quotations.amount','quotations.amount_with_iva')
                ->get();
            }else if(isset($typeinvoice) && ($typeinvoice == 'facturas')){
                $quotations = DB::connection(Auth::user()->database_name)->table('quotations')
                ->join('clients', 'clients.id','=','quotations.id_client')
                ->leftjoin('anticipos', 'anticipos.id_quotation','=','quotations.id')
                ->whereIn('quotations.status',[1,'P'])
                ->where('quotations.amount','<>',null)
                ->where('quotations.date_quotation','<=',$date_consult)
                ->where('quotations.date_billing','<>',null)
                ->where('quotations.id_client',$id_client)
                ->select('quotations.date_quotation','quotations.id','quotations.serie','clients.name as name_client','quotations.amount','quotations.amount_with_iva', DB::raw('SUM(anticipos.amount) As amount_anticipo'))
                ->groupBy('quotations.date_quotation','quotations.id','quotations.serie','clients.name','quotations.amount','quotations.amount_with_iva')
                ->get();
            }else
            {
                $quotations = DB::connection(Auth::user()->database_name)->table('quotations')
                                    ->join('clients', 'clients.id','=','quotations.id_client')
                                    ->leftjoin('anticipos', 'anticipos.id_quotation','=','quotations.id')
                                    ->whereIn('quotations.status',[1,'P'])
                                    ->where('quotations.amount','<>',null)
                                    ->where('quotations.date_quotation','<=',$date_consult)
                                    ->where('quotations.id_client',$id_client)
                                    ->select('quotations.date_quotation','quotations.id','quotations.serie','clients.name as name_client','quotations.amount','quotations.amount_with_iva', DB::raw('SUM(anticipos.amount) As amount_anticipo'))
                                    ->groupBy('quotations.date_quotation','quotations.id','quotations.serie','clients.name','quotations.amount','quotations.amount_with_iva')
                                    ->get();
            }
        }else{
            if(isset($typeinvoice) && ($typeinvoice == 'notas')){
                $quotations = DB::connection(Auth::user()->database_name)->table('quotations')
                ->join('clients', 'clients.id','=','quotations.id_client')
                ->leftjoin('anticipos', 'anticipos.id_quotation','=','quotations.id')
                ->whereIn('quotations.status',[1,'P'])
                ->where('quotations.amount','<>',null)
                ->where('quotations.date_quotation','<=',$date_consult)
                ->where('quotations.date_delivery_note','<>',null)
                ->select('quotations.date_quotation','quotations.id','quotations.serie','clients.name as name_client','quotations.amount','quotations.amount_with_iva', DB::raw('SUM(anticipos.amount) As amount_anticipo'))
                ->groupBy('quotations.date_quotation','quotations.id','quotations.serie','clients.name','quotations.amount','quotations.amount_with_iva')
                ->get();
            }else if(isset($typeinvoice) && ($typeinvoice == 'facturas')){
                $quotations = DB::connection(Auth::user()->database_name)->table('quotations')
                ->join('clients', 'clients.id','=','quotations.id_client')
                ->leftjoin('anticipos', 'anticipos.id_quotation','=','quotations.id')
                ->whereIn('quotations.status',[1,'P'])
                ->where('quotations.amount','<>',null)
                ->where('quotations.date_quotation','<=',$date_consult)
                ->where('quotations.date_billing','<>',null)
                ->select('quotations.date_quotation','quotations.id','quotations.serie','clients.name as name_client','quotations.amount','quotations.amount_with_iva', DB::raw('SUM(anticipos.amount) As amount_anticipo'))
                ->groupBy('quotations.date_quotation','quotations.id','quotations.serie','clients.name','quotations.amount','quotations.amount_with_iva')
                ->get();
            }else
            {
                $quotations = DB::connection(Auth::user()->database_name)->table('quotations')
                                    ->join('clients', 'clients.id','=','quotations.id_client')
                                    ->leftjoin('anticipos', 'anticipos.id_quotation','=','quotations.id')
                                    ->whereIn('quotations.status',[1,'P'])
                                    ->where('quotations.amount','<>',null)
                                    ->where('quotations.date_quotation','<=',$date_consult)
                                    ->select('quotations.date_quotation','quotations.id','quotations.serie','clients.name as name_client','quotations.amount','quotations.amount_with_iva', DB::raw('SUM(anticipos.amount) As amount_anticipo'))
                                    ->groupBy('quotations.date_quotation','quotations.id','quotations.serie','clients.name','quotations.amount','quotations.amount_with_iva')
                                    ->get();
            }
        }
        
        $pdf = $pdf->loadView('admin.reports.accounts_receivable',compact('quotations','datenow','date_end'));
        return $pdf->stream();
                 
    }


    function debtstopay_pdf($date_end = null,$id_provider = null)
    {
      
        $pdf = App::make('dompdf.wrapper');

        
        $date = Carbon::now();
        $datenow = $date->format('d-m-Y'); 
        if(empty($date_end)){
            $date_end = $datenow;

            $date_consult = $date->format('Y-m-d'); 
        }else{
            $date_end = Carbon::parse($date_end)->format('d-m-Y');

            $date_consult = Carbon::parse($date_end)->format('Y-m-d');
        }
        
        $period = $date->format('Y'); 
        
      
        if(isset($id_provider)){
            $expenses = DB::connection(Auth::user()->database_name)->table('expenses_and_purchases')
                                    ->join('providers', 'providers.id','=','expenses_and_purchases.id_provider')
                                    ->leftjoin('anticipos', 'anticipos.id_expense','=','expenses_and_purchases.id')
                                    ->whereIn('expenses_and_purchases.status',[1,'P'])
                                    ->where('expenses_and_purchases.amount','<>',null)
                                    ->where('expenses_and_purchases.date','<=',$date_consult)
                                    ->where('expenses_and_purchases.id_provider',$id_provider)
                                    ->select('expenses_and_purchases.date','expenses_and_purchases.id','expenses_and_purchases.serie','providers.razon_social as name_provider','expenses_and_purchases.amount','expenses_and_purchases.amount_with_iva', DB::raw('SUM(anticipos.amount) As amount_anticipo'))
                                    ->groupBy('expenses_and_purchases.date','expenses_and_purchases.id','expenses_and_purchases.serie','providers.razon_social','expenses_and_purchases.amount','expenses_and_purchases.amount_with_iva')
                                    ->get();
        }else{
            $expenses = DB::connection(Auth::user()->database_name)->table('expenses_and_purchases')
                                    ->join('providers', 'providers.id','=','expenses_and_purchases.id_provider')
                                    ->leftjoin('anticipos', 'anticipos.id_expense','=','expenses_and_purchases.id')
                                    ->whereIn('expenses_and_purchases.status',[1,'P'])
                                    ->where('expenses_and_purchases.amount','<>',null)
                                    ->where('expenses_and_purchases.date','<=',$date_consult)
                                    ->select('expenses_and_purchases.date','expenses_and_purchases.id','expenses_and_purchases.serie','providers.razon_social as name_provider','expenses_and_purchases.amount','expenses_and_purchases.amount_with_iva', DB::raw('SUM(anticipos.amount) As amount_anticipo'))
                                    ->groupBy('expenses_and_purchases.date','expenses_and_purchases.id','expenses_and_purchases.serie','providers.razon_social','expenses_and_purchases.amount','expenses_and_purchases.amount_with_iva')
                                    ->get();
        }
        
        $pdf = $pdf->loadView('admin.reports.debtstopay',compact('expenses','datenow','date_end'));
        return $pdf->stream();
                 
    }


    function ledger_pdf($date_begin = null,$date_end = null)
    {
      
        $pdf = App::make('dompdf.wrapper');

        $company = Company::on(Auth::user()->database_name)->find(1);
        
        $date = Carbon::now();
        $datenow = $date->format('Y-m-d'); 
        $period = $date->format('Y'); 
       
        if(isset($date_begin)){
            $from = $date_begin;
        }
        if(isset($date_end)){
            $to = $date_end;
        }else{
            $to = $datenow;
        }

        $details = DB::connection(Auth::user()->database_name)->table('detail_vouchers')
                ->join('accounts', 'accounts.id','=','detail_vouchers.id_account')
                ->join('header_vouchers', 'header_vouchers.id','=','detail_vouchers.id_header_voucher')
                ->select('accounts.code_one','accounts.code_two','accounts.code_three'
                        ,'accounts.code_four','accounts.code_five','accounts.description as account_description'
                        ,'detail_vouchers.debe','detail_vouchers.haber'
                        ,'header_vouchers.description as header_description'
                        ,'header_vouchers.id as id_header'
                        ,'header_vouchers.date as date')
                ->orderBy('accounts.code_one','asc')
                ->orderBy('accounts.code_two','asc')
                ->orderBy('accounts.code_three','asc')
                ->orderBy('accounts.code_four','asc')
                ->orderBy('accounts.code_five','asc')
                ->get();
        
        $pdf = $pdf->loadView('admin.reports.ledger',compact('company','datenow','details','date_begin','date_end'));
        return $pdf->stream();
                 
    }


    function balance_ingresos_pdf($date_begin = null,$date_end = null,$level = null,$coin = null){
      
        $pdf = App::make('dompdf.wrapper');

        
        $date = Carbon::now();
        $datenow = $date->format('Y-m-d'); 
        $period = $date->format('Y'); 
        $detail_old = DetailVoucher::on(Auth::user()->database_name)->orderBy('created_at','asc')->first();

        if(isset($date_begin)){
            $from = $date_begin;
        }else{
            $from = $detail_old->created_at->format('Y-m-d');
        }
        if(isset($date_end)){
            $to = $date_end;
        }else{
            $to = $datenow;
        }
        if(isset($level)){
            
        }else{
            $level = 5;
        }

        if(isset($coin) && ($coin == "bolivares")){
            $accounts = $this->calculation($from,$to);
        }else{
            $accounts = $this->calculation_dolar("dolares");
        }

        $pdf = $pdf->loadView('admin.reports.ingresos_egresos',compact('coin','datenow','accounts','level','detail_old','date_begin','date_end'));
        return $pdf->stream();
                 
    }


    function retencion_iva_expense($date_begin = null,$date_end = null,$level = null)
    {
      
        $pdf = App::make('dompdf.wrapper');

        
        $date = Carbon::now();
        $datenow = $date->format('Y-m-d'); 
        $period = $date->format('Y'); 
        $detail_old = DetailVoucher::on(Auth::user()->database_name)->orderBy('created_at','asc')->first();

        if(isset($date_begin)){
            $from = $date_begin;
        }else{
            $from = $detail_old->created_at->format('Y-m-d');
        }
        if(isset($date_end)){
            $to = $date_end;
        }else{
            $to = $datenow;
        }
        if(isset($level)){
            
        }else{
            $level = 5;
        }

        $accounts_all = $this->calculation($from,$to);

        $accounts = $accounts_all->filter(function($account)
        {
            if($account->code_one <= 3){
                $total = $account->balance_previus + $account->debe - $account->haber;
                if ($total != 0) {
                    return $account;
                }
            }
            
        });

        $pdf = $pdf->loadView('admin.reports.balance_general',compact('datenow','accounts','level','detail_old','date_begin','date_end'));
        return $pdf->stream();
                 
    }
    //agregar que retorne el monto en dolares
    public function calculation($date_begin,$date_end)
    {
        
        //dd($date_begin);
        $accounts = Account::on(Auth::user()->database_name)->orderBy('code_one', 'asc')
                         ->orderBy('code_two', 'asc')
                         ->orderBy('code_three', 'asc')
                         ->orderBy('code_four', 'asc')
                         ->orderBy('code_five', 'asc')
                         ->get();

                       
                         if(isset($accounts)) {
                            foreach ($accounts as $var) {
                 
                                    
                                if($var->code_one != 0){
                                    
                                    if($var->code_two != 0){
                    
                    
                                        if($var->code_three != 0){
                    
                    
                                            if($var->code_four != 0){

                                                if($var->code_five != 0){
                                                    //Calculo de superavit
                                                    if(($var->code_one == 3) && ($var->code_two == 2) && ($var->code_three == 1) && 
                                                    ($var->code_four == 1) && ($var->code_five == 1) ){
                                                        $var = $this->calculation_superavit($var,4,'bolivares',$date_begin,$date_end);
                                                    }else{
                                                        /*CALCULA LOS SALDOS DESDE DETALLE COMPROBANTE */                                                   
                                                        $total_debe = DB::connection(Auth::user()->database_name)->table('accounts')
                                                        ->join('detail_vouchers', 'detail_vouchers.id_account', '=', 'accounts.id')
                                                        ->where('accounts.code_one', $var->code_one)
                                                        ->where('accounts.code_two', $var->code_two)
                                                        ->where('accounts.code_three', $var->code_three)
                                                        ->where('accounts.code_four', $var->code_four)
                                                        ->where('accounts.code_five', $var->code_five)
                                                        ->where('detail_vouchers.status', 'C')
                                                        //->whereBetween('detail_vouchers.created_at', [$date_begin, $date_end])
                                                        ->whereRaw(
                                                        "(DATE_FORMAT(detail_vouchers.created_at, '%Y-%m-%d') >= ? AND DATE_FORMAT(detail_vouchers.created_at, '%Y-%m-%d') <= ?)", 
                                                        [$date_begin, $date_end])
                                                        ->sum('debe');
                                                        
                                                       

                                                        $total_haber = DB::connection(Auth::user()->database_name)->table('accounts')
                                                        ->join('detail_vouchers', 'detail_vouchers.id_account', '=', 'accounts.id')
                                                        ->where('accounts.code_one', $var->code_one)
                                                        ->where('accounts.code_two', $var->code_two)
                                                        ->where('accounts.code_three', $var->code_three)
                                                        ->where('accounts.code_four', $var->code_four)
                                                        ->where('accounts.code_five', $var->code_five)
                                                        ->where('detail_vouchers.status', 'C')
                                                        //->whereBetween('detail_vouchers.created_at', [$date_begin, $date_end])
                                                        ->whereRaw(
                                                        "(DATE_FORMAT(detail_vouchers.created_at, '%Y-%m-%d') >= ? AND DATE_FORMAT(detail_vouchers.created_at, '%Y-%m-%d') <= ?)", 
                                                        [$date_begin, $date_end])
                                                        ->sum('haber');   

                                                        
                                                        /*---------------------------------------------------*/

                                                

                                                        $var->debe = $total_debe;
                                                        $var->haber = $total_haber;
                                                    }
                                                }else
                                                {
                                                    /*CALCULA LOS SALDOS DESDE DETALLE COMPROBANTE */                                                   
                                                    $total_debe = DB::connection(Auth::user()->database_name)->table('accounts')
                                                                        ->join('detail_vouchers', 'detail_vouchers.id_account', '=', 'accounts.id')
                                                                        ->where('accounts.code_one', $var->code_one)
                                                                        ->where('accounts.code_two', $var->code_two)
                                                                        ->where('accounts.code_three', $var->code_three)
                                                                        ->where('accounts.code_four', $var->code_four)
                                                                        ->where('detail_vouchers.status', 'C')
                                                                        //->whereBetween('detail_vouchers.created_at', [$date_begin, $date_end])
                                                                        ->whereRaw(
                                                    "(DATE_FORMAT(detail_vouchers.created_at, '%Y-%m-%d') >= ? AND DATE_FORMAT(detail_vouchers.created_at, '%Y-%m-%d') <= ?)", 
                                                    [$date_begin, $date_end])
                                                                        ->sum('debe');
                    
                                                    $total_haber = DB::connection(Auth::user()->database_name)->table('accounts')
                                                                        ->join('detail_vouchers', 'detail_vouchers.id_account', '=', 'accounts.id')
                                                                        ->where('accounts.code_one', $var->code_one)
                                                                        ->where('accounts.code_two', $var->code_two)
                                                                        ->where('accounts.code_three', $var->code_three)
                                                                        ->where('accounts.code_four', $var->code_four)
                                                                        ->where('detail_vouchers.status', 'C')
                                                                        //->whereBetween('detail_vouchers.created_at', [$date_begin, $date_end])
                                                                        ->whereRaw(
                                                    "(DATE_FORMAT(detail_vouchers.created_at, '%Y-%m-%d') >= ? AND DATE_FORMAT(detail_vouchers.created_at, '%Y-%m-%d') <= ?)", 
                                                    [$date_begin, $date_end])
                                                                        ->sum('haber');   

                                                    $total_balance = DB::connection(Auth::user()->database_name)->table('accounts')
                                                                        ->where('accounts.code_one', $var->code_one)
                                                                        ->where('accounts.code_two', $var->code_two)
                                                                        ->where('accounts.code_three', $var->code_three)
                                                                        ->where('accounts.code_four', $var->code_four)
                                                                        ->sum('balance_previus');   
                                                    /*---------------------------------------------------*/
                 
                                           
                 
                                                    $var->debe = $total_debe;
                                                    $var->haber = $total_haber;
                                                    $var->balance_previus = $total_balance;
                   
                                                }
                                                                           
                    
                                            }else{
                                               
                                              
                                          
                                                    /*CALCULA LOS SALDOS DESDE DETALLE COMPROBANTE */ 
                                                        $total_debe = DB::connection(Auth::user()->database_name)->table('accounts')
                                                                        ->join('detail_vouchers', 'detail_vouchers.id_account', '=', 'accounts.id')
                                                                        ->where('accounts.code_one', $var->code_one)
                                                                        ->where('accounts.code_two', $var->code_two)
                                                                        ->where('accounts.code_three', $var->code_three)
                                                                        ->where('detail_vouchers.status', 'C')
                                                                        //->whereBetween('detail_vouchers.created_at', [$date_begin, $date_end])
                                                                        ->whereRaw(
                                                                        "(DATE_FORMAT(detail_vouchers.created_at, '%Y-%m-%d') >= ? AND DATE_FORMAT(detail_vouchers.created_at, '%Y-%m-%d') <= ?)", 
                                                                        [$date_begin, $date_end])
                                                                        ->sum('debe');
                                
                                                        $total_haber =  DB::connection(Auth::user()->database_name)->table('accounts')
                                                                        ->join('detail_vouchers', 'detail_vouchers.id_account', '=', 'accounts.id')
                                                                        ->where('accounts.code_one', $var->code_one)
                                                                        ->where('accounts.code_two', $var->code_two)
                                                                        ->where('accounts.code_three', $var->code_three)
                                                                        ->where('detail_vouchers.status', 'C')
                                                                        //->whereBetween('detail_vouchers.created_at', [$date_begin, $date_end])
                                                                        ->whereRaw(
                                                                        "(DATE_FORMAT(detail_vouchers.created_at, '%Y-%m-%d') >= ? AND DATE_FORMAT(detail_vouchers.created_at, '%Y-%m-%d') <= ?)", 
                                                                        [$date_begin, $date_end])
                                                                        ->sum('haber');    
                                                                        
                                                                        $total_balance = DB::connection(Auth::user()->database_name)->table('accounts')
                                                                        ->where('accounts.code_one', $var->code_one)
                                                                        ->where('accounts.code_two', $var->code_two)
                                                                        ->where('accounts.code_three', $var->code_three)
                                                                        ->sum('balance_previus');   
                                                    /*---------------------------------------------------*/                               
                            
                                                    
                            
                                                    $var->debe = $total_debe;
                                                    $var->haber = $total_haber;       
                                                    $var->balance_previus = $total_balance;
                                                
                                                        
                                                }
                                                    }else{
                                                        
                                                
                                                    /*CALCULA LOS SALDOS DESDE DETALLE COMPROBANTE */                                   
                                                        $total_debe = DB::connection(Auth::user()->database_name)->table('accounts')
                                                                                        ->join('detail_vouchers', 'detail_vouchers.id_account', '=', 'accounts.id')
                                                                                        ->where('accounts.code_one', $var->code_one)
                                                                                        ->where('accounts.code_two', $var->code_two)
                                                                                        ->where('detail_vouchers.status', 'C')
                                                                                        //->whereBetween('detail_vouchers.created_at', [$date_begin, $date_end])
                                                                                        ->whereRaw(
                                                                                        "(DATE_FORMAT(detail_vouchers.created_at, '%Y-%m-%d') >= ? AND DATE_FORMAT(detail_vouchers.created_at, '%Y-%m-%d') <= ?)", 
                                                                                        [$date_begin, $date_end])
                                                                                        ->sum('debe');
                                
                                                    
                                                        $total_haber = DB::connection(Auth::user()->database_name)->table('accounts')
                                                                                        ->join('detail_vouchers', 'detail_vouchers.id_account', '=', 'accounts.id')
                                                                                        ->where('accounts.code_one', $var->code_one)
                                                                                        ->where('accounts.code_two', $var->code_two)
                                                                                        ->where('detail_vouchers.status', 'C')
                                                                                        //->whereBetween('detail_vouchers.created_at', [$date_begin, $date_end])
                                                                                        ->whereRaw(
                                                                                        "(DATE_FORMAT(detail_vouchers.created_at, '%Y-%m-%d') >= ? AND DATE_FORMAT(detail_vouchers.created_at, '%Y-%m-%d') <= ?)", 
                                                                                        [$date_begin, $date_end])
                                                                                        ->sum('haber');

                                                        $total_balance = DB::connection(Auth::user()->database_name)->table('accounts')
                                                                                        ->where('accounts.code_one', $var->code_one)
                                                                                        ->where('accounts.code_two', $var->code_two)
                                                                                        ->sum('balance_previus'); 
                                                    /*---------------------------------------------------*/
                                                    
                                                    $var->debe = $total_debe;
                                                    $var->haber = $total_haber;
                                                    $var->balance_previus = $total_balance;
                                                    
                                                    }
                                                }else{
                                                    //Cuentas NIVEL 2 EJEMPLO 1.0.0.0
                                                    /*CALCULA LOS SALDOS DESDE DETALLE COMPROBANTE */
                                                    if($var->code_one == 3){
                                                        $var = $this->calculation_capital($var,'bolivares',$date_begin,$date_end);
                                                    
                                                    }else{
                                                        $total_debe = DB::connection(Auth::user()->database_name)->table('accounts')
                                                                                    ->join('detail_vouchers', 'detail_vouchers.id_account', '=', 'accounts.id')
                                                                                    ->where('accounts.code_one', $var->code_one)
                                                                                    ->where('detail_vouchers.status', 'C')
                                                                                    //->whereBetween('detail_vouchers.created_at', [$date_begin, $date_end])
                                                                                    ->whereRaw(
                                                                                    "(DATE_FORMAT(detail_vouchers.created_at, '%Y-%m-%d') >= ? AND DATE_FORMAT(detail_vouchers.created_at, '%Y-%m-%d') <= ?)", 
                                                                                    [$date_begin, $date_end])
                                                                                    ->sum('debe');
                                
                                                    
                                                    
                                                        $total_haber = DB::connection(Auth::user()->database_name)->table('accounts')
                                                                                    ->join('detail_vouchers', 'detail_vouchers.id_account', '=', 'accounts.id')
                                                                                    ->where('accounts.code_one', $var->code_one)
                                                                                    ->where('detail_vouchers.status', 'C')
                                                                                    //->whereBetween('detail_vouchers.created_at', [$date_begin, $date_end])
                                                                                    ->whereRaw(
                                                                                    "(DATE_FORMAT(detail_vouchers.created_at, '%Y-%m-%d') >= ? AND DATE_FORMAT(detail_vouchers.created_at, '%Y-%m-%d') <= ?)", 
                                                                                    [$date_begin, $date_end])
                                                                                    ->sum('haber');
                                                        $total_balance = DB::connection(Auth::user()->database_name)->table('accounts')
                                                                                    ->where('accounts.code_one', $var->code_one)
                                                                                    ->sum('balance_previus'); 
                                                        /*---------------------------------------------------*/
                            
                                                    
                                                        $var->debe = $total_debe;
                                                        $var->haber = $total_haber;           
                                                        $var->balance_previus = $total_balance;
                                                    }
                                                }
                                            }else{
                                                return redirect('/accounts')->withDanger('El codigo uno es igual a cero!');
                                            }
                                        } 
                                    }  else{
                                        return redirect('/accounts')->withDanger('No hay Cuentas');
                                    }              
                 
       
        
         return $accounts;
    }


    public function calculation_capital($var,$coin,$date_begin,$date_end)
    {
        $total_debe = DB::connection(Auth::user()->database_name)->table('accounts')
                                    ->join('detail_vouchers', 'detail_vouchers.id_account', '=', 'accounts.id')
                                    ->where('accounts.code_one','>=', $var->code_one)
                                    ->where('detail_vouchers.status', 'C')
                                    //->whereBetween('detail_vouchers.created_at', [$date_begin, $date_end])
                                    ->whereRaw(
                                    "(DATE_FORMAT(detail_vouchers.created_at, '%Y-%m-%d') >= ? AND DATE_FORMAT(detail_vouchers.created_at, '%Y-%m-%d') <= ?)", 
                                    [$date_begin, $date_end])
                                    ->sum('debe');

    
    
        $total_haber = DB::connection(Auth::user()->database_name)->table('accounts')
                                    ->join('detail_vouchers', 'detail_vouchers.id_account', '=', 'accounts.id')
                                    ->where('accounts.code_one','>=', $var->code_one)
                                    ->where('detail_vouchers.status', 'C')
                                    //->whereBetween('detail_vouchers.created_at', [$date_begin, $date_end])
                                    ->whereRaw(
                                    "(DATE_FORMAT(detail_vouchers.created_at, '%Y-%m-%d') >= ? AND DATE_FORMAT(detail_vouchers.created_at, '%Y-%m-%d') <= ?)", 
                                    [$date_begin, $date_end])
                                    ->sum('haber');
        $total_balance = DB::connection(Auth::user()->database_name)->table('accounts')
                                    ->where('accounts.code_one', $var->code_one)
                                    ->sum('balance_previus'); 
        /*---------------------------------------------------*/

    
        $var->debe = $total_debe;
        $var->haber = $total_haber;           
        $var->balance_previus = $total_balance;

        return $var;
    }

    public function calculation_superavit($var,$code,$coin,$date_begin,$date_end)
    {
        $total_debe = DB::connection(Auth::user()->database_name)->table('accounts')
                ->join('detail_vouchers', 'detail_vouchers.id_account', '=', 'accounts.id')
                ->where('accounts.code_one','>=', $code)
                ->where('detail_vouchers.status', 'C')
                ->whereRaw(
                "(DATE_FORMAT(detail_vouchers.created_at, '%Y-%m-%d') >= ? AND DATE_FORMAT(detail_vouchers.created_at, '%Y-%m-%d') <= ?)", 
                [$date_begin, $date_end])
                ->sum('debe');



        $total_haber = DB::connection(Auth::user()->database_name)->table('accounts')
                ->join('detail_vouchers', 'detail_vouchers.id_account', '=', 'accounts.id')
                ->where('accounts.code_one','>=', $code)
                ->where('detail_vouchers.status', 'C')
                //->whereBetween('detail_vouchers.created_at', [$date_begin, $date_end])
                ->whereRaw(
                "(DATE_FORMAT(detail_vouchers.created_at, '%Y-%m-%d') >= ? AND DATE_FORMAT(detail_vouchers.created_at, '%Y-%m-%d') <= ?)", 
                [$date_begin, $date_end])
                ->sum('haber');


        $var->debe = $total_debe;
        $var->haber = $total_haber;    
        
 
         return $var;
 
    }
 
    public function calculation_dolar($coin)
    {
        
        $accounts = Account::on(Auth::user()->database_name)->orderBy('code_one', 'asc')
                         ->orderBy('code_two', 'asc')
                         ->orderBy('code_three', 'asc')
                         ->orderBy('code_four', 'asc')
                         ->orderBy('code_five', 'asc')
                         ->get();
        
                       
        if(isset($accounts)) {
            
            foreach ($accounts as $var) 
            {
                if($var->code_one != 0)
                {
                    if($var->code_two != 0)
                    {
                        if($var->code_three != 0)
                        {
                            if($var->code_four != 0)
                            {
                                if($var->code_five != 0)
                                {
                                    //Calculo de superavit
                                    if(($var->code_one == 3) && ($var->code_two == 2) && ($var->code_three == 1) && 
                                    ($var->code_four == 1) && ($var->code_five == 1) ){
                                        $var = $this->calculation_superavit_dolar($var,4,$coin);
                                    }else{
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
                                                        d.status = ?
                                                        '
                                                        , [$var->code_one,$var->code_two,$var->code_three,$var->code_four,$var->code_five,'C']);
                                        $total_haber =   DB::connection(Auth::user()->database_name)->select('SELECT SUM(d.haber) AS haber
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
                                                        , [$var->code_one,$var->code_two,$var->code_three,$var->code_four,$var->code_five,'C']);
    
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
                                                        , [$var->code_one,$var->code_two,$var->code_three,$var->code_four,$var->code_five,'C']);
    
                                        $total_dolar_haber =   DB::connection(Auth::user()->database_name)->select('SELECT SUM(d.haber/d.tasa) AS dolar
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
                                                        , [$var->code_one,$var->code_two,$var->code_three,$var->code_four,$var->code_five,'C']);
    
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
                                            d.status = ?
                                            '
                                            , [$var->code_one,$var->code_two,$var->code_three,$var->code_four,$var->code_five,'C']);
                                            
                                            $total_haber =   DB::connection(Auth::user()->database_name)->select('SELECT SUM(d.haber/d.tasa) AS haber
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
                                            , [$var->code_one,$var->code_two,$var->code_three,$var->code_four,$var->code_five,'C']);
    
                                           
                                            if(($var->balance_previus != 0) && ($var->rate !=0)){
                                                $var->balance =  $var->balance_previus / ($var->rate ?? 1);
                                                $var->balance_previus = $var->balance;
                                            }
                                            
                                        }
                                        $total_debe = $total_debe[0]->debe;
                                        $total_haber = $total_haber[0]->haber;
                                        if(isset($total_dolar_debe[0]->dolar)){
                                            $total_dolar_debe = $total_dolar_debe[0]->dolar;
                                            $var->dolar_debe = $total_dolar_debe;
                                        }
                                        if(isset($total_dolar_haber[0]->dolar)){
                                            $total_dolar_haber = $total_dolar_haber[0]->dolar;
                                            $var->dolar_haber = $total_dolar_haber;
                                        }
                                    
                                        $var->debe = $total_debe;
                                        $var->haber = $total_haber;

                                       
                                    }

                                }else{
                            
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
                                                    d.status = ?
                                                    '
                                                    , [$var->code_one,$var->code_two,$var->code_three,$var->code_four,'C']);
                                    $total_haber =   DB::connection(Auth::user()->database_name)->select('SELECT SUM(d.haber) AS haber
                                                    FROM accounts a
                                                    INNER JOIN detail_vouchers d 
                                                        ON d.id_account = a.id
                                                    WHERE a.code_one = ? AND
                                                    a.code_two = ? AND
                                                    a.code_three = ? AND
                                                    a.code_four = ? AND
                                                    d.status = ?
                                                    '
                                                    , [$var->code_one,$var->code_two,$var->code_three,$var->code_four,'C']);

                                    $total_dolar_debe =   DB::connection(Auth::user()->database_name)->select('SELECT SUM(d.debe/d.tasa) AS dolar
                                                    FROM accounts a
                                                    INNER JOIN detail_vouchers d 
                                                        ON d.id_account = a.id
                                                    WHERE a.code_one = ? AND
                                                    a.code_two = ? AND
                                                    a.code_three = ? AND
                                                    a.code_four = ? AND
                                                    d.status = ?
                                                    '
                                                    , [$var->code_one,$var->code_two,$var->code_three,$var->code_four,'C']);

                                    $total_dolar_haber =   DB::connection(Auth::user()->database_name)->select('SELECT SUM(d.haber/d.tasa) AS dolar
                                                    FROM accounts a
                                                    INNER JOIN detail_vouchers d 
                                                        ON d.id_account = a.id
                                                    WHERE a.code_one = ? AND
                                                    a.code_two = ? AND
                                                    a.code_three = ? AND
                                                    a.code_four = ? AND
                                                    d.status = ?
                                                    '
                                                    , [$var->code_one,$var->code_two,$var->code_three,$var->code_four,'C']);

                                                    $var->balance =  $var->balance_previus;

                                    $total_balance =   DB::connection(Auth::user()->database_name)->select('SELECT SUM(a.balance_previus) AS balance
                                                    FROM accounts a
                                                    WHERE a.code_one = ? AND
                                                    a.code_two = ?  AND
                                                    a.code_three = ? AND
                                                    a.code_four = ?
                                                    '
                                                    , [$var->code_one,$var->code_two,$var->code_three,$var->code_four]);
                                
                                    }else{
                                        $total_debe =   DB::connection(Auth::user()->database_name)->select('SELECT SUM(d.debe/d.tasa) AS debe
                                        FROM accounts a
                                        INNER JOIN detail_vouchers d 
                                            ON d.id_account = a.id
                                        WHERE a.code_one = ? AND
                                        a.code_two = ? AND
                                        a.code_three = ? AND
                                        a.code_four = ? AND
                                        d.status = ?
                                        '
                                        , [$var->code_one,$var->code_two,$var->code_three,$var->code_four,'C']);
                                        
                                        $total_haber =   DB::connection(Auth::user()->database_name)->select('SELECT SUM(d.haber/d.tasa) AS haber
                                        FROM accounts a
                                        INNER JOIN detail_vouchers d 
                                            ON d.id_account = a.id
                                        WHERE a.code_one = ? AND
                                        a.code_two = ? AND
                                        a.code_three = ? AND
                                        a.code_four = ? AND
                                        d.status = ?
                                        '
                                        , [$var->code_one,$var->code_two,$var->code_three,$var->code_four,'C']);

                                        $total_balance =   DB::connection(Auth::user()->database_name)->select('SELECT SUM(a.balance_previus/a.rate) AS balance
                                                    FROM accounts a
                                                    WHERE a.code_one = ? AND
                                                    a.code_two = ?  AND
                                                    a.code_three = ? AND
                                                    a.code_four = ?
                                                    '
                                                    , [$var->code_one,$var->code_two,$var->code_three,$var->code_four]);

                                        /*if(($var->balance_previus != 0) && ($var->rate !=0))
                                        $var->balance =  $var->balance_previus / $var->rate;*/
                                    }
                                    $total_debe = $total_debe[0]->debe;
                                    $total_haber = $total_haber[0]->haber;
                                    if(isset($total_dolar_debe[0]->dolar)){
                                        $total_dolar_debe = $total_dolar_debe[0]->dolar;
                                        $var->dolar_debe = $total_dolar_debe;
                                    }
                                    if(isset($total_dolar_haber[0]->dolar)){
                                        $total_dolar_haber = $total_dolar_haber[0]->dolar;
                                        $var->dolar_haber = $total_dolar_haber;
                                    }
                                
                                    $var->debe = $total_debe;
                                    $var->haber = $total_haber;

                                    $total_balance = $total_balance[0]->balance;
                                    $var->balance = $total_balance;
                                    $var->balance_previus = $total_balance;
                                }  
                            }else{          
                            
                                if($coin == 'bolivares'){
                                $total_debe =   DB::connection(Auth::user()->database_name)->select('SELECT SUM(d.debe) AS debe
                                                FROM accounts a
                                                INNER JOIN detail_vouchers d 
                                                    ON d.id_account = a.id
                                                WHERE a.code_one = ? AND
                                                a.code_two = ? AND
                                                a.code_three = ? AND
                                                
                                                d.status = ?
                                                '
                                                , [$var->code_one,$var->code_two,$var->code_three,'C']);
                                $total_haber =   DB::connection(Auth::user()->database_name)->select('SELECT SUM(d.haber) AS haber
                                                FROM accounts a
                                                INNER JOIN detail_vouchers d 
                                                    ON d.id_account = a.id
                                                WHERE a.code_one = ? AND
                                                a.code_two = ? AND
                                                a.code_three = ? AND
                                                
                                                d.status = ?
                                                '
                                                , [$var->code_one,$var->code_two,$var->code_three,'C']);

                                $total_balance =   DB::connection(Auth::user()->database_name)->select('SELECT SUM(a.balance_previus) AS balance
                                            FROM accounts a
                                            WHERE a.code_one = ? AND
                                            a.code_two = ?  AND
                                            a.code_three = ?
                                            '
                                            , [$var->code_one,$var->code_two,$var->code_three]);
                                
                                }else{
                                        $total_debe =   DB::connection(Auth::user()->database_name)->select('SELECT SUM(d.debe/d.tasa) AS debe
                                        FROM accounts a
                                        INNER JOIN detail_vouchers d 
                                            ON d.id_account = a.id
                                        WHERE a.code_one = ? AND
                                        a.code_two = ? AND
                                        a.code_three = ? AND
                                        
                                        d.status = ?
                                        '
                                        , [$var->code_one,$var->code_two,$var->code_three,'C']);
                                        
                                        $total_haber =   DB::connection(Auth::user()->database_name)->select('SELECT SUM(d.haber/d.tasa) AS haber
                                        FROM accounts a
                                        INNER JOIN detail_vouchers d 
                                            ON d.id_account = a.id
                                        WHERE a.code_one = ? AND
                                        a.code_two = ? AND
                                        a.code_three = ? AND
                                        
                                        d.status = ?
                                        '
                                        , [$var->code_one,$var->code_two,$var->code_three,'C']);
                        
                                        $total_balance =   DB::connection(Auth::user()->database_name)->select('SELECT SUM(a.balance_previus/a.rate) AS balance
                                            FROM accounts a
                                            WHERE a.code_one = ? AND
                                            a.code_two = ? AND
                                            a.code_three = ?
                                            '
                                            , [$var->code_one,$var->code_two,$var->code_three]);

                                    }
                                    $total_debe = $total_debe[0]->debe;
                                    $total_haber = $total_haber[0]->haber;
                                
                                    $var->debe = $total_debe;
                                    $var->haber = $total_haber;

                                    

                                    $total_balance = $total_balance[0]->balance;
                                    $var->balance = $total_balance;
                                    $var->balance_previus = $total_balance;
                                            
                            }           
                        }else{
                                            
                            if($coin == 'bolivares'){
                                $total_debe =   DB::connection(Auth::user()->database_name)->select('SELECT SUM(d.debe) AS debe
                                                FROM accounts a
                                                INNER JOIN detail_vouchers d 
                                                    ON d.id_account = a.id
                                                WHERE a.code_one = ? AND
                                                a.code_two = ? AND
                                                d.status = ?
                                                '
                                                , [$var->code_one,$var->code_two,'C']);
                                $total_haber =   DB::connection(Auth::user()->database_name)->select('SELECT SUM(d.haber) AS haber
                                                FROM accounts a
                                                INNER JOIN detail_vouchers d 
                                                    ON d.id_account = a.id
                                                WHERE a.code_one = ? AND
                                                a.code_two = ? AND
                                                d.status = ?
                                                '
                                                , [$var->code_one,$var->code_two,'C']);
                                
                                $total_balance =   DB::connection(Auth::user()->database_name)->select('SELECT SUM(a.balance_previus) AS balance
                                            FROM accounts a
                                            WHERE a.code_one = ? AND
                                            a.code_two = ?
                                            '
                                            , [$var->code_one,$var->code_two]);
                                
                                }else{
                                    $total_debe =   DB::connection(Auth::user()->database_name)->select('SELECT SUM(d.debe/d.tasa) AS debe
                                    FROM accounts a
                                    INNER JOIN detail_vouchers d 
                                        ON d.id_account = a.id
                                    WHERE a.code_one = ? AND
                                    a.code_two = ? AND
                                    d.status = ?
                                    '
                                    , [$var->code_one,$var->code_two,'C']);
                                    
                                    $total_haber =   DB::connection(Auth::user()->database_name)->select('SELECT SUM(d.haber/d.tasa) AS haber
                                    FROM accounts a
                                    INNER JOIN detail_vouchers d 
                                        ON d.id_account = a.id
                                    WHERE a.code_one = ? AND
                                    a.code_two = ? AND
                                    d.status = ?
                                    '
                                    , [$var->code_one,$var->code_two,'C']);

                                    $total_balance =   DB::connection(Auth::user()->database_name)->select('SELECT SUM(a.balance_previus/a.rate) AS balance
                                            FROM accounts a
                                            WHERE a.code_one = ? AND
                                            a.code_two = ?
                                            '
                                            , [$var->code_one,$var->code_two]);
                    
                                }
                                
                                $total_debe = $total_debe[0]->debe;
                                $total_haber = $total_haber[0]->haber;
                                $var->debe = $total_debe;
                                $var->haber = $total_haber;

                                

                                $total_balance = $total_balance[0]->balance;
                                $var->balance = $total_balance;
                                $var->balance_previus = $total_balance;
                        }
                    }else{
                        //Calcular patrimonio con las cuentas mayores o iguales a 3.0.0.0.0
                        if($var->code_one == 3){
                            $var = $this->calculation_capital_dolar($var,$coin);
                           
                        }else{
                            if($coin == 'bolivares'){
                                $total_debe =   DB::connection(Auth::user()->database_name)->select('SELECT SUM(d.debe) AS debe
                                                FROM accounts a
                                                INNER JOIN detail_vouchers d 
                                                    ON d.id_account = a.id
                                                WHERE a.code_one = ? AND
                                                d.status = ?
                                                '
                                                , [$var->code_one,'C']);
                                $total_haber =   DB::connection(Auth::user()->database_name)->select('SELECT SUM(d.haber) AS haber
                                                FROM accounts a
                                                INNER JOIN detail_vouchers d 
                                                    ON d.id_account = a.id
                                                WHERE a.code_one = ? AND
                                                d.status = ?
                                                '
                                                , [$var->code_one,'C']);
    
                                $total_balance =   DB::connection(Auth::user()->database_name)->select('SELECT SUM(a.balance_previus) AS balance
                                                FROM accounts a
                                                WHERE a.code_one = ?
                                                '
                                                , [$var->code_one]);
                                
                                }else{
                                    $total_debe =   DB::connection(Auth::user()->database_name)->select('SELECT SUM(d.debe/d.tasa) AS debe
                                    FROM accounts a
                                    INNER JOIN detail_vouchers d 
                                        ON d.id_account = a.id
                                    WHERE a.code_one = ? AND
                                    d.status = ?
                                    '
                                    , [$var->code_one,'C']);
                                    
                                    $total_haber =   DB::connection(Auth::user()->database_name)->select('SELECT SUM(d.haber/d.tasa) AS haber
                                    FROM accounts a
                                    INNER JOIN detail_vouchers d 
                                        ON d.id_account = a.id
                                    WHERE a.code_one = ? AND
                                    d.status = ?
                                    '
                                    , [$var->code_one,'C']);
    
                                    $total_balance =   DB::connection(Auth::user()->database_name)->select('SELECT SUM(a.balance_previus/a.rate) AS balance
                                                FROM accounts a
                                                WHERE a.code_one = ?
                                                '
                                                , [$var->code_one]);
                    
                                }
                                $total_debe = $total_debe[0]->debe;
                                $total_haber = $total_haber[0]->haber;
                                $var->debe = $total_debe;
                                $var->haber = $total_haber;
    
                                $total_balance = $total_balance[0]->balance;
    
                                $var->balance = $total_balance;
                                $var->balance_previus = $total_balance;
                        }
                    }
                }else{
                    return redirect('/accounts/menu')->withDanger('El codigo uno es igual a cero!');
                }
            } 
        
        }else{
            return redirect('/accounts/menu')->withDanger('No hay Cuentas');
        }              
                 
       
        
         return $accounts;
    }

    public function calculation_capital_dolar($var,$coin)
    {
        if($coin == 'bolivares'){
            $total_debe =   DB::connection(Auth::user()->database_name)->select('SELECT SUM(d.debe) AS debe
                            FROM accounts a
                            INNER JOIN detail_vouchers d 
                                ON d.id_account = a.id
                            WHERE a.code_one >= ? AND
                            d.status = ?
                            '
                            , [$var->code_one,'C']);
            $total_haber =   DB::connection(Auth::user()->database_name)->select('SELECT SUM(d.haber) AS haber
                            FROM accounts a
                            INNER JOIN detail_vouchers d 
                                ON d.id_account = a.id
                            WHERE a.code_one >= ? AND
                            d.status = ?
                            '
                            , [$var->code_one,'C']);

            $total_balance =   DB::connection(Auth::user()->database_name)->select('SELECT SUM(a.balance_previus) AS balance
                            FROM accounts a
                            WHERE a.code_one = ?
                            '
                            , [$var->code_one]);
            
            }else{
                $total_debe =   DB::connection(Auth::user()->database_name)->select('SELECT SUM(d.debe/d.tasa) AS debe
                FROM accounts a
                INNER JOIN detail_vouchers d 
                    ON d.id_account = a.id
                WHERE a.code_one >= ? AND
                d.status = ?
                '
                , [$var->code_one,'C']);
                
                $total_haber =   DB::connection(Auth::user()->database_name)->select('SELECT SUM(d.haber/d.tasa) AS haber
                FROM accounts a
                INNER JOIN detail_vouchers d 
                    ON d.id_account = a.id
                WHERE a.code_one >= ? AND
                d.status = ?
                '
                , [$var->code_one,'C']);

                $total_balance =   DB::connection(Auth::user()->database_name)->select('SELECT SUM(a.balance_previus/a.rate) AS balance
                            FROM accounts a
                            WHERE a.code_one = ?
                            '
                            , [$var->code_one]);

            }
            $total_debe = $total_debe[0]->debe;
            $total_haber = $total_haber[0]->haber;
            $var->debe = $total_debe;
            $var->haber = $total_haber;

            $total_balance = $total_balance[0]->balance;

            $var->balance = $total_balance;
            $var->balance_previus = $total_balance;

            return $var;
    }

    
    public function calculation_superavit_dolar($var,$code,$coin)
   {
    if($coin == 'bolivares'){
        $total_debe =   DB::connection(Auth::user()->database_name)->select('SELECT SUM(d.debe) AS debe
                        FROM accounts a
                        INNER JOIN detail_vouchers d 
                            ON d.id_account = a.id
                        WHERE a.code_one >= ? AND
                        d.status = ?
                        '
                        , [$code,'C']);
        $total_haber =   DB::connection(Auth::user()->database_name)->select('SELECT SUM(d.haber) AS haber
                        FROM accounts a
                        INNER JOIN detail_vouchers d 
                            ON d.id_account = a.id
                        WHERE a.code_one >= ? AND
                        d.status = ?
                        '
                        , [$code,'C']);

        
        }else{
            $total_debe =   DB::connection(Auth::user()->database_name)->select('SELECT SUM(d.debe/d.tasa) AS debe
            FROM accounts a
            INNER JOIN detail_vouchers d 
                ON d.id_account = a.id
            WHERE a.code_one >= ? AND
            d.status = ?
            '
            , [$code,'C']);
            
            $total_haber =   DB::connection(Auth::user()->database_name)->select('SELECT SUM(d.haber/d.tasa) AS haber
            FROM accounts a
            INNER JOIN detail_vouchers d 
                ON d.id_account = a.id
            WHERE a.code_one >= ? AND
            d.status = ?
            '
            , [$code,'C']);


            // por ahora se dejara el balance aqui en cero, ya que asi cuadra
            $var->balance = 0;
            $var->balance_previus = 0;
            /*if(($var->balance_previus != 0) && ($var->rate !=0)){
            $var->balance =  $var->balance_previus / ($var->rate ?? 1);
            $var->balance_previus = $var->balance;
            }*/
        }
        $total_debe = $total_debe[0]->debe;
        $total_haber = $total_haber[0]->haber;
        $var->debe = $total_debe;
        $var->haber = $total_haber;

       
        //$total_balance = $total_balance[0]->balance;

        //$var->balance = $total_balance;

        return $var;

   }


    public function select_client()
    {


            $clients    = Client::on(Auth::user()->database_name)->get();
        
            return view('admin.reports.selectclient',compact('clients'));
    }

    public function select_provider()
    {
        $providers    = Provider::on(Auth::user()->database_name)->get();
    
        return view('admin.reports.selectprovider',compact('providers'));
    }
    
}