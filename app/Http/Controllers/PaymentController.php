<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use Illuminate\Http\Request;


use App;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

use App\Company;
use App\DetailVoucher;
use App\Multipayment;
use App\Quotation;
use App\QuotationPayment;

class PaymentController extends Controller
{
    public function index()
    {
        
        $user       =   auth()->user();
        $users_role =   $user->role_id;

        $payment_quotations = null;
        
        $date = Carbon::now();
        $datenow = $date->format('Y-m-d');    
        $datebeginyear = $date->firstOfYear()->format('Y-m-d');
        
        $payment_quotations = QuotationPayment::on(Auth::user()->database_name)
                                ->whereRaw(
                                    "(DATE_FORMAT(created_at, '%Y-%m-%d') >= ? AND DATE_FORMAT(created_at, '%Y-%m-%d') <= ?)", 
                                    [$datebeginyear, $datenow])
                                ->where('status','1')->orderBy('created_at','desc')->get();

        foreach($payment_quotations as $payment_quotation){

            $type = $this->asignar_payment_type($payment_quotation->payment_type);

            $payment_quotation->type = $type;
        }
            
        
        return view('admin.payments.index',compact('datenow','payment_quotations'));
      
    }


    public function movements($id_invoice)
    {
        

        $user       =   auth()->user();
        $users_role =   $user->role_id;
        
            $quotation = Quotation::on(Auth::user()->database_name)->find($id_invoice);
            $detailvouchers = DetailVoucher::on(Auth::user()->database_name)
                                            ->join('header_vouchers','header_vouchers.id','detail_vouchers.id_header_voucher')
                                            ->where('id_invoice',$id_invoice)
                                            ->where('header_vouchers.description','LIKE','Cobro%')
                                            ->get();

            $multipayments_detail = null;
            $invoices = null;
            $coin = $quotation->coin;
            $return = "payments";

            //Buscamos a la factura para luego buscar atraves del header a la otras facturas
            $multipayment = Multipayment::on(Auth::user()->database_name)->where('id_quotation',$id_invoice)->first();
            if(isset($multipayment)){
            $invoices = Multipayment::on(Auth::user()->database_name)->where('id_header',$multipayment->id_header)->get();
            $multipayments_detail = DetailVoucher::on(Auth::user()->database_name)->where('id_header_voucher',$multipayment->id_header)->get();
            }

            if(!isset($coin)){
                $coin = 'bolivares';
            }
         
        
        return view('admin.invoices.index_detail_movement',compact('return','detailvouchers','quotation','coin','invoices','multipayments_detail'));
    }


    function pdf($id_quotation,$coin)
    {
        
        $pdf = App::make('dompdf.wrapper');

        $date = Carbon::now();
        $datenow = $date->format('Y-m-d');    
       
        $movement = Quotation::on(Auth::user()->database_name)
        ->join('quotation_payments', 'quotation_payments.id_quotation', '=', 'quotations.id')
        ->join('detail_vouchers', 'detail_vouchers.id_invoice', '=', 'quotations.id')
        ->join('header_vouchers','header_vouchers.id','detail_vouchers.id_header_voucher')
        ->join('accounts','accounts.id','detail_vouchers.id_account')
        ->where('quotations.id',$id_quotation)

        ->select('header_vouchers.description', 'detail_vouchers.debe', 'detail_vouchers.haber', 'detail_vouchers.haber',
        'accounts.code_one','accounts.code_two','accounts.code_three','accounts.code_four','accounts.code_five',
        'detail_vouchers.tasa')
        ->get();
           

        $company = Company::on(Auth::user()->database_name)->find(1);
        //Si la taza es automatica
        if($company->tiporate_id == 1){
            $rate = $this->search_bcv();
        }else{
            //si la tasa es fija
            $rate = $company->rate;
        }

       
        $pdf = $pdf->loadView('admin.payments.pdf',compact('coin','rate','movement','datenow'));
        return $pdf->stream();
                 
    }


    function asignar_payment_type($type){
      
        if($type == 1){
            return "Cheque";
        }
        if($type == 2){
            return "Contado";
        }
        if($type == 3){
            return "Contra Anticipo";
        }
        if($type == 4){
            return "Crédito";
        }
        if($type == 5){
            return "Depósito Bancario";
        }
        if($type == 6){
            return "Efectivo";
        }
        if($type == 7){
            return "Indeterminado";
        }
        if($type == 8){
            return "Tarjeta Coorporativa";
        }
        if($type == 9){
            return "Tarjeta de Crédito";
        }
        if($type == 10){
            return "Tarjeta de Débito";
        }
        if($type == 11){
            return "Transferencia";
        }
    }

}
