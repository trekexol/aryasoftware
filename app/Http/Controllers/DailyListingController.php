<?php

namespace App\Http\Controllers;

use App;
use App\Account;
use App\Company;
use App\DetailVoucher;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class DailyListingController extends Controller
{
    public function index()
    {
        $user       =   auth()->user();
        $users_role =   $user->role_id;
        if($users_role == '1'){
         
        $date = Carbon::now();
        $datenow = $date->format('Y-m-d');
        $detailvouchers = DB::connection(Auth::user()->database_name)->table('detail_vouchers')
                            ->join('header_vouchers', 'header_vouchers.id', '=', 'detail_vouchers.id_header_voucher')
                            ->join('accounts', 'accounts.id', '=', 'detail_vouchers.id_account')
                            ->where('header_vouchers.date', $datenow)
                            ->select('detail_vouchers.*','header_vouchers.*'
                            ,'accounts.description as account_description')->get();
            
        $accounts = Account::on(Auth::user()->database_name)->select('id','description')->where('code_one','<>',0)
                            ->where('code_two','<>',0)
                            ->where('code_three','<>',0)
                            ->where('code_four','<>',0)
                            ->where('code_five', '<>',0)
                            ->get();
                            


        }elseif($users_role == '2'){
            return view('admin.index',compact('detailvouchers'));
        }
 
        return view('admin.daily_listing.index',compact('detailvouchers','datenow','accounts'));
    }


    public function store(Request $request)
    {
        $data = request()->validate([
            
        
            'date_begin'        =>'required',
            'date_end'  =>'required',
           
        
        
        ]);
        
        $date_begin = request('date_begin');
        $date_end = request('date_end');

        $detailvouchers =  DB::connection(Auth::user()->database_name)->table('detail_vouchers')
                                ->join('header_vouchers', 'header_vouchers.id', '=', 'detail_vouchers.id_header_voucher')
                                ->join('accounts', 'accounts.id', '=', 'detail_vouchers.id_account')
                                ->whereBetween('header_vouchers.date', [$date_begin, $date_end])
                                ->select('detail_vouchers.*','header_vouchers.*'
                                ,'accounts.description as account_description')
                                ->orderBy('detail_vouchers.id','desc')->get();

        $accounts = Account::on(Auth::user()->database_name)->select('id','description')->where('code_one','<>',0)
                                ->where('code_two','<>',0)
                                ->where('code_three','<>',0)
                                ->where('code_four','<>',0)
                                ->where('code_five', '<>',0)
                                ->get();
                                
    
                
        return view('admin.daily_listing.index',compact('detailvouchers','date_begin','date_end','accounts'));
   
    }

    public function print_journalbook(Request $request)
    {
        $date_begin = request('date_begin');
        $date_end = request('date_end');

        $date = Carbon::now();
        $datenow = $date->format('d-m-Y');

        $pdf = App::make('dompdf.wrapper');

        $company = Company::on(Auth::user()->database_name)->find(1);

        $detailvouchers =  DB::connection(Auth::user()->database_name)->table('detail_vouchers')
                            ->join('header_vouchers', 'header_vouchers.id', '=', 'detail_vouchers.id_header_voucher')
                            ->join('accounts', 'accounts.id', '=', 'detail_vouchers.id_account')
                            ->whereBetween('header_vouchers.date', [$date_begin, $date_end])
                            ->select('detail_vouchers.*','header_vouchers.*'
                            ,'accounts.description as account_description'
                            ,'header_vouchers.id as id_header'
                            ,'header_vouchers.description as header_description')->get();

        
        //dd($detailvouchers);
        $date_begin = Carbon::parse($date_begin)->format('d-m-Y');

        $date_end = Carbon::parse($date_end)->format('d-m-Y');

        

        $pdf = $pdf->loadView('admin.reports.journal_book',compact('company','detailvouchers'
                                ,'datenow','date_begin','date_end'));
        return $pdf->stream();
    }


    public function print_diary_book_detail(Request $request)
    {
        $id_account = request('id_account');
        $coin = request('coin');

        
        $date_begin = request('date_begin');
        $date_end = request('date_end');

        $date = Carbon::now();
        $datenow = $date->format('d-m-Y');

        $pdf = App::make('dompdf.wrapper');

        $company = Company::on(Auth::user()->database_name)->find(1);

        $detailvouchers =  DB::connection(Auth::user()->database_name)->table('detail_vouchers')
                            ->join('header_vouchers', 'header_vouchers.id', '=', 'detail_vouchers.id_header_voucher')
                            ->join('accounts', 'accounts.id', '=', 'detail_vouchers.id_account')
                            ->whereBetween('header_vouchers.date', [$date_begin, $date_end])
                            ->where('accounts.id',$id_account)
                            ->select('detail_vouchers.*','header_vouchers.*'
                            ,'accounts.description as account_description'
                            ,'header_vouchers.id as id_header'
                            ,'header_vouchers.description as header_description')
                            ->orderBy('detail_vouchers.id','asc')->get();


        //busca los saldos previos de la cuenta                    
        $detailvouchers_saldo_debe =  DB::connection(Auth::user()->database_name)->table('detail_vouchers')
                            ->join('header_vouchers', 'header_vouchers.id', '=', 'detail_vouchers.id_header_voucher')
                            ->join('accounts', 'accounts.id', '=', 'detail_vouchers.id_account')
                            ->where('header_vouchers.date','<' ,$date_begin)
                            ->where('accounts.id',$id_account)
                            ->select('detail_vouchers.*','header_vouchers.*'
                            ,'accounts.description as account_description'
                            ,'header_vouchers.id as id_header'
                            ,'header_vouchers.description as header_description')
                            ->sum('detail_vouchers.debe');

        $detailvouchers_saldo_haber =  DB::connection(Auth::user()->database_name)->table('detail_vouchers')
                            ->join('header_vouchers', 'header_vouchers.id', '=', 'detail_vouchers.id_header_voucher')
                            ->join('accounts', 'accounts.id', '=', 'detail_vouchers.id_account')
                            ->where('header_vouchers.date','<' ,$date_begin)
                            ->where('accounts.id',$id_account)
                            ->select('detail_vouchers.*','header_vouchers.*'
                            ,'accounts.description as account_description'
                            ,'header_vouchers.id as id_header'
                            ,'header_vouchers.description as header_description')
                            ->sum('detail_vouchers.haber');       
        //-----------------------------------------------

        $date_begin = Carbon::parse($date_begin)->format('d-m-Y');

        $date_end = Carbon::parse($date_end)->format('d-m-Y');

        $account = Account::on(Auth::user()->database_name)->find($id_account);



        $saldo_anterior = ($account->balance_previus ?? 0) + ($detailvouchers_saldo_debe ?? 0) - ($detailvouchers_saldo_haber ?? 0);
        $primer_movimiento = true;
        $saldo = 0;

        foreach($detailvouchers as $detail){
            if($primer_movimiento){
                $detail->saldo = $detail->debe - $detail->haber + $saldo_anterior;
                $saldo += $detail->saldo;
                $primer_movimiento = false;
            }else{
                $detail->saldo = $detail->debe - $detail->haber + $saldo;
                $saldo = $detail->saldo;
            }
            
        }

        //voltea los movimientos para mostrarlos del mas actual al mas antiguo
        $detailvouchers = array_reverse($detailvouchers->toArray());

        
        $pdf = $pdf->loadView('admin.reports.diary_book_detail',compact('company','detailvouchers'
                                ,'datenow','date_begin','date_end','account'
                                ,'detailvouchers_saldo_debe','detailvouchers_saldo_haber','saldo'));
        return $pdf->stream();
    }
   
    

}
