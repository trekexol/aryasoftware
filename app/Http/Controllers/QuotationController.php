<?php

namespace App\Http\Controllers;

use App\Anticipo;
use App\Client;
use App\Company;
use App\DetailVoucher;
use App\Exports\ProductsExport;
use App\Http\Controllers\UserAccess\UserAccessController;
use App\Inventory;
use App\Multipayment;
use App\Product;
use App\Quotation;
use App\QuotationPayment;
use App\QuotationProduct;
use App\Transport;
use App\UserAccess;
use App\Vendor;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;


class QuotationController extends Controller
{
    public $userAccess;
    public $modulo = 'Cotizacion';

 
    public function __construct(){

       $this->middleware('auth');
       $this->userAccess = new UserAccessController();
   }

   public function index()
   {
        
        if($this->userAccess->validate_user_access($this->modulo)){
            $quotations = Quotation::on(Auth::user()->database_name)->orderBy('id' ,'DESC')
            ->where('date_billing','=',null)
            ->where('date_delivery_note','=',null)
            ->where('date_order','=',null)
            ->get();

            return view('admin.quotations.index',compact('quotations'));
        }else{
            return redirect('/home')->withDanger('No tiene Acceso al modulo de '.$this->modulo);
        }

      
   }

   /**
    * Show the form for creating a new resource.
    *
    * @return \Illuminate\Http\Response
    */
    
    public function createquotation()
    {
        $transports     = Transport::on(Auth::user()->database_name)->get();

        $date = Carbon::now();
        $datenow = $date->format('Y-m-d');    

        return view('admin.quotations.createquotation',compact('datenow','transports'));
    }

    public function createquotationclient($id_client)
    {
        $client = null;
                
        if(isset($id_client)){
            $client = Client::on(Auth::user()->database_name)->find($id_client);
        }
        if(isset($client)){

        /* $vendors     = Vendor::on(Auth::user()->database_name)->get();*/

            $transports     = Transport::on(Auth::user()->database_name)->get();

            $date = Carbon::now();
            $datenow = $date->format('Y-m-d');    

            return view('admin.quotations.createquotation',compact('client','datenow','transports'));

        }else{
            return redirect('/quotations')->withDanger('El Cliente no existe');
        } 
    }

    public function createquotationvendor($id_client,$id_vendor)
    {
        $client = null;
                
        if(isset($id_client)){
            $client = Client::on(Auth::user()->database_name)->find($id_client);
        }
        if(isset($client)){

            $vendor = null;
                
            if(isset($id_vendor)){
                $vendor = Vendor::on(Auth::user()->database_name)->find($id_vendor);
            }
            if(isset($vendor)){

                /* $vendors     = Vendor::on(Auth::user()->database_name)->get();*/

                $transports     = Transport::on(Auth::user()->database_name)->get();

                $date = Carbon::now();
                $datenow = $date->format('Y-m-d');    

                return view('admin.quotations.createquotation',compact('client','vendor','datenow','transports'));

            }else{
                return redirect('/quotations')->withDanger('El Vendedor no existe');
            } 

        }else{
            return redirect('/quotations')->withDanger('El Cliente no existe');
        } 
    }



    public function create($id_quotation,$coin)
    {
        
        if($this->userAccess->validate_user_access($this->modulo)){
            $quotation = null;
                
            if(isset($id_quotation)){
                $quotation = Quotation::on(Auth::user()->database_name)->find($id_quotation);
            }

            if(isset($quotation) && ($quotation->status == 1)){
                //$inventories_quotations = QuotationProduct::on(Auth::user()->database_name)->where('id_quotation',$quotation->id)->get();
                $inventories_quotations = DB::connection(Auth::user()->database_name)->table('products')
                                ->join('inventories', 'products.id', '=', 'inventories.product_id')
                                ->join('quotation_products', 'inventories.id', '=', 'quotation_products.id_inventory')
                                ->where('quotation_products.id_quotation',$id_quotation)
                                ->select('products.*','quotation_products.price as price','quotation_products.rate as rate','quotation_products.id as quotation_products_id','inventories.code as code','quotation_products.discount as discount',
                                'quotation_products.amount as amount_quotation')
                                ->get(); 
            
                
                $date = Carbon::now();
                $datenow = $date->format('Y-m-d');  

                $company = Company::on(Auth::user()->database_name)->find(1);

                //Si la taza es automatica
                if($company->tiporate_id == 1){
                    //esto es para que siempre se pueda guardar la tasa en la base de datos
                    $bcv_quotation_product = $this->search_bcv();
                    $bcv = $this->search_bcv();
                }else{
                    //si la tasa es fija
                    $bcv_quotation_product = $company->rate;
                    $bcv = $company->rate;

                }
               
                if(($coin == 'bolivares') ){
                    
                    $coin = 'bolivares';
                }else{
                    //$bcv = null;

                    $coin = 'dolares';
                }
                
        
                return view('admin.quotations.create',compact('quotation','inventories_quotations','datenow','bcv','coin','bcv_quotation_product'));
            }else{
                return redirect('/quotations')->withDanger('No es posible ver esta cotizacion');
            } 
            
        }else{
            return redirect('/home')->withDanger('No tiene Acceso al modulo de '.$this->modulo);
        }

    }

    
    public function search_bcv()
    {
        /*Buscar el indice bcv*/
        $urlToGet ='http://www.bcv.org.ve/tasas-informativas-sistema-bancario';
        $pageDocument = @file_get_contents($urlToGet);
        preg_match_all('|<div class="col-sm-6 col-xs-6 centrado"><strong> (.*?) </strong> </div>|s', $pageDocument, $cap);

        if ($cap[0] == array()){ // VALIDAR Concidencia
            $titulo = '0,00';
        }else {
            $titulo = $cap[1][4];
        }

        $bcv_con_formato = $titulo;
        $bcv = str_replace(',', '.', str_replace('.', '',$bcv_con_formato));


        /*-------------------------- */
        return $bcv;

    }


    public function createproduct($id_quotation,$coin,$id_inventory)
    {
        $quotation = null;
                
        if(isset($id_quotation)){
            $quotation = Quotation::on(Auth::user()->database_name)->find($id_quotation);
        }

        if(isset($quotation) && ($quotation->status == 1)){
            //$product_quotations = QuotationProduct::on(Auth::user()->database_name)->where('id_quotation',$quotation->id)->get();
                $product = null;
                $inventories_quotations = DB::connection(Auth::user()->database_name)->table('products')
                                ->join('inventories', 'products.id', '=', 'inventories.product_id')
                                ->join('quotation_products', 'inventories.id', '=', 'quotation_products.id_inventory')
                                ->where('quotation_products.id_quotation',$id_quotation)
                                ->select('products.*','quotation_products.price as price','quotation_products.rate as rate','quotation_products.id as quotation_products_id','inventories.code as code','quotation_products.discount as discount',
                                'quotation_products.amount as amount_quotation')
                                ->get(); 
                
                if(isset($id_inventory)){
                    $inventory = Inventory::on(Auth::user()->database_name)->find($id_inventory);
                }
                if(isset($inventory)){

                    $date = Carbon::now();
                    $datenow = $date->format('Y-m-d');    
                    
                    /*Revisa si la tasa de la empresa es automatica o fija*/
                    $company = Company::on(Auth::user()->database_name)->find(1);
                    //Si la taza es automatica
                    if($company->tiporate_id == 1){
                        $bcv_quotation_product = $this->search_bcv();
                    }else{
                        //si la tasa es fija
                        $bcv_quotation_product = $company->rate;
                    }


                    if(($coin == 'bolivares')){
                        
                        if($company->tiporate_id == 1){
                            $bcv = $this->search_bcv();
                        }else{
                            //si la tasa es fija
                            $bcv = $company->rate;
                        }
                    }else{
                        //Cuando mi producto esta en Bolivares, pero estoy cotizando en dolares, convierto los bs a dolares
                        if($inventory->products['money'] == 'Bs'){
                            $inventory->products['price'] = $inventory->products['price'] / $quotation->bcv;
                        }
                        $bcv = null;
                    }
                    $llego ='llego';

                    return view('admin.quotations.create',compact('llego','bcv_quotation_product','quotation','inventories_quotations','inventory','bcv','datenow','coin'));

                }else{
                    return redirect('/quotations')->withDanger('El Producto no existe');
                } 
        }else{
            return redirect('/quotations')->withDanger('La cotizacion no existe');
        } 

    }

    public function selectproduct($id_quotation,$coin,$type)
    {

        $services = null;

        $inventories = DB::connection(Auth::user()->database_name)->table('inventories')
            ->join('products', 'products.id', '=', 'inventories.product_id')
            ->where(function ($query){
                $query->where('products.type','MERCANCIA')
                    ->orWhere('products.type','COMBO');
            })
            ->where('products.status',1)
            ->select('products.*','inventories.amount as amount','inventories.id as id_inventory')
            ->orderBy('products.code_comercial','desc')
            ->get();
        
        $quotation = Quotation::on(Auth::user()->database_name)->find($id_quotation);

        $bcv_quotation_product = $quotation->bcv;
        
        $company = Company::on(Auth::user()->database_name)->find(1);
        //Si la taza es automatica
        if($company->tiporate_id == 1){
            $bcv = $this->search_bcv();
        }else{
            //si la tasa es fija
            $bcv = $company->rate;
        }

        if(($type == 'servicios') || $inventories->isEmpty()){

            $type = 'servicios';
            $services = DB::connection(Auth::user()->database_name)->table('inventories')
            ->join('products', 'products.id', '=', 'inventories.product_id')
            ->where('products.type','SERVICIO')
            ->where('products.status',1)
            ->select('products.*','inventories.id as id_inventory')
            ->orderBy('products.code_comercial','desc')
            ->get();
            
            return view('admin.quotations.selectservice',compact('type','services','id_quotation','coin','bcv','bcv_quotation_product'));
        }
    
        return view('admin.quotations.selectinventary',compact('type','inventories','id_quotation','coin','bcv','bcv_quotation_product'));
    }


    public function createvendor($id_product,$id_vendor)
    {

            $vendor = null;
            
            if(isset($id_vendor)){
                $vendor = vendor::on(Auth::user()->database_name)->find($id_vendor);
            }

            $clients     = Client::on(Auth::user()->database_name)->get();
        
            $vendors     = Vendor::on(Auth::user()->database_name)->get();

            $transports     = Transport::on(Auth::user()->database_name)->get();

            $date = Carbon::now();
            $datenow = $date->format('Y-m-d');    

            return view('admin.quotations.create',compact('clients','vendors','datenow','transports','vendor'));
    }

    public function selectvendor($id_client)
    {
            if($id_client != -1){

                $vendors     = vendor::on(Auth::user()->database_name)->get();

                
        
                return view('admin.quotations.selectvendor',compact('vendors','id_client'));

            }else{
                return redirect('/quotations/registerquotation')->withDanger('Seleccione un Cliente primero');
            }

        
    }

    public function selectclient()
    {
        $clients     = Client::on(Auth::user()->database_name)->orderBy('name','asc')->get();
    
        return view('admin.quotations.selectclient',compact('clients'));
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
            
        
            'id_client'         =>'required',
            'id_transport'         =>'required',
            'id_user'         =>'required',
            'date_quotation'         =>'required',
        
        ]);

        $id_client = request('id_client');
        $id_vendor = request('id_vendor');

        
        if($id_client != '-1'){
            
                $var = new Quotation();
                $var->setConnection(Auth::user()->database_name);

                $var->id_client = $id_client;
                $var->id_vendor = $id_vendor;

                $id_transport = request('id_transport');
                if($id_transport != '-1'){
                    $var->id_transport = request('id_transport');
                }
                
                $var->id_user = request('id_user');
                $var->serie = request('serie');
                $var->date_quotation = request('date_quotation');
        
                $var->observation = request('observation');
                $var->note = request('note');

                $company = Company::on(Auth::user()->database_name)->find(1);
                //Si la taza es automatica
                if($company->tiporate_id == 1){
                    $bcv = $this->search_bcv();
                }else{
                    //si la tasa es fija
                    $bcv = $company->rate;
                }

                $var->bcv = $bcv;

                $var->coin = 'bolivares';
        
                $var->status =  1;
            
                $var->save();
        
                return redirect('quotations/register/'.$var->id.'/bolivares');

            
        }else{
            return redirect('/quotations/registerquotation')->withDanger('Debe Buscar un Cliente');
        } 

        
    }


    public function storeproduct(Request $request)
    {
        
        $data = request()->validate([
            
        
            'id_quotation'         =>'required',
            'id_inventory'         =>'required',
            'amount'         =>'required',
            'discount'         =>'required',
        
        
        ]);

        
        $var = new QuotationProduct();
        $var->setConnection(Auth::user()->database_name);

        $var->id_quotation = request('id_quotation');
        
        $var->id_inventory = request('id_inventory');

        $islr = request('islr');
        if($islr == null){
            $var->retiene_islr = false;
        }else{
            $var->retiene_islr = true;
        }

        $exento = request('exento');
        if($exento == null){
            $var->retiene_iva = false;
        }else{
            $var->retiene_iva = true;
        }

        $coin = request('coin');

        $quotation = Quotation::on(Auth::user()->database_name)->find($var->id_quotation);

        $var->rate = $quotation->bcv;

        if($var->id_inventory == -1){
            return redirect('quotations/register/'.$var->id_quotation.'')->withDanger('No se encontro el producto!');
            /*echo '<script type="text/javascript">alert("no encontrado sin cantidad");</script>';
            return;*/
        }

        $amount = request('amount');
        $cost = str_replace(',', '.', str_replace('.', '',request('cost')));

        $global = new GlobalController();
        
        $value_return = $global->check_amount($quotation->id,$var->id_inventory,$amount);

        if($value_return != 'exito'){
                return redirect('quotations/registerproduct/'.$var->id_quotation.'/'.$coin.'/'.$var->id_inventory.'')->withDanger('La cantidad de este producto excede a la cantidad puesta en inventario!');
        }

        

        if($coin == 'dolares'){
            $cost_sin_formato = ($cost) * $var->rate;
        }else{
            $cost_sin_formato = $cost;
        }

        $var->price = $cost_sin_formato;
        

        $var->amount = $amount;

        $var->discount = request('discount');

        if(($var->discount < 0) || ($var->discount > 100)){
            return redirect('quotations/register/'.$var->id_quotation.'/'.$coin.'/'.$var->id_inventory.'')->withDanger('El descuento debe estar entre 0% y 100%!');
        }
        
        $var->status =  1;
    
        $var->save();

        return redirect('quotations/register/'.$var->id_quotation.'/'.$coin.'')->withSuccess('Producto agregado Exitosamente!');
    }
    /**
        * Display the specified resource.
        *
        * @param  int  $id
        * @return \Illuminate\Http\Response
        */

   


    public function show($id)
    {
        //
    }

    /**
        * Show the form for editing the specified resource.
        *
        * @param  int  $id
        * @return \Illuminate\Http\Response
        */
    public function edit($id)
    {
        $quotation = quotation::on(Auth::user()->database_name)->find($id);
    
        return view('admin.quotations.edit',compact('quotation'));
    
    }
    public function editquotationproduct($id,$coin = null)
    {
            $quotation_product = QuotationProduct::on(Auth::user()->database_name)->find($id);
        
            if(isset($quotation_product)){

                $inventory= Inventory::on(Auth::user()->database_name)->find($quotation_product->id_inventory);

                $company = Company::on(Auth::user()->database_name)->find(1);
                //Si la taza es automatica
                if($company->tiporate_id == 1){
                    $bcv = $this->search_bcv();
                }else{
                    //si la tasa es fija
                    $bcv = $company->rate;
                }

                if(!isset($coin)){
                    $coin = 'bolivares';
                }

                if($coin == 'bolivares'){
                    $rate = null;
                }else{
                    $rate = $quotation_product->rate;
                }

                return view('admin.quotations.edit_product',compact('rate','coin','quotation_product','inventory','bcv'));
            }else{
                return redirect('/quotations')->withDanger('No se Encontro el Producto!');
            }
        
        
    
    }
    

    /**
        * Update the specified resource in storage.
        *
        * @param  \Illuminate\Http\Request  $request
        * @param  int  $id
        * @return \Illuminate\Http\Response
        */
    public function update(Request $request, $id)
    {

        $vars =  Quotation::on(Auth::user()->database_name)->find($id);

        $vars_status = $vars->status;
        $vars_exento = $vars->exento;
        $vars_islr = $vars->islr;
    
        $data = request()->validate([
            
        
            'segment_id'         =>'required',
            'sub_segment_id'         =>'required',
            'unit_of_measure_id'         =>'required',


            'type'         =>'required',
            'description'         =>'required',
        
            'price'         =>'required',
            'price_buy'         =>'required',
            'cost_average'         =>'required',

            'money'         =>'required',
        
            'special_impuesto'         =>'required',
            'status'         =>'required',
        
        ]);

        $var = Quotation::on(Auth::user()->database_name)->findOrFail($id);

        $var->segment_id = request('segment_id');
        $var->subsegment_id= request('sub_segment_id');
        $var->unit_of_measure_id = request('unit_of_measure_id');

        $var->code_comercial = request('code_comercial');
        $var->type = request('type');
        $var->description = request('description');
        
        $var->price = request('price');
        $var->price_buy = request('price_buy');
    
        $var->cost_average = request('cost_average');
        $var->photo_quotation = request('photo_quotation');

        $var->money = request('money');


        $var->special_impuesto = request('special_impuesto');

        if(request('exento') == null){
            $var->exento = "0";
        }else{
            $var->exento = "1";
        }
        if(request('islr') == null){
            $var->islr = "0";
        }else{
            $var->islr = "1";
        }
    

        if(request('status') == null){
            $var->status = $vars_status;
        }else{
            $var->status = request('status');
        }
    
        $var->save();

        return redirect('/quotations')->withSuccess('Actualizacion Exitosa!');
        }



        

        public function updatequotationproduct(Request $request, $id)
        { 

           
            $data = request()->validate([
                
                'amount'         =>'required',
                'discount'         =>'required',
            
            ]);

            
        
            $var = QuotationProduct::on(Auth::user()->database_name)->findOrFail($id);

            $sin_formato_price = str_replace(',', '.', str_replace('.', '', request('price')));
            $sin_formato_rate = str_replace(',', '.', str_replace('.', '', request('rate')));

            $coin = request('coin');
            $var->rate = $sin_formato_rate;

            if($coin == 'bolivares'){
                $var->price = $sin_formato_price;
            }else{
                $var->price = $sin_formato_price * $sin_formato_rate;
            }
        
            $var->amount = request('amount');
        
            $var->discount = request('discount');
        
            $global = new GlobalController();

            $value_return = $global->check_amount($var->id_quotation,$var->id_inventory,$var->amount);


            $islr = request('islr');
            if($islr == null){
                $var->retiene_islr = false;
            }else{
                $var->retiene_islr = true;
            }

            $exento = request('exento');
            if($exento == null){
                $var->retiene_iva = false;
            }else{
                $var->retiene_iva = true;
            }

            if($value_return != 'exito'){
                return redirect('quotations/quotationproduct/'.$var->id.'/'.$coin.'/edit')->withDanger('La cantidad de este producto excede a la cantidad puesta en inventario! ');
            }

        
            $var->save();
        
            return redirect('/quotations/register/'.$var->id_quotation.'/'.$coin.'')->withSuccess('Actualizacion Exitosa!');
        
        }


        public function refreshrate($id_quotation,$coin,$rate)
        { 
            $sin_formato_rate = str_replace(',', '.', str_replace('.', '', $rate));


            QuotationProduct::on(Auth::user()->database_name)->where('id_quotation',$id_quotation)
                                    ->update(['rate' => $sin_formato_rate]);
        

            Quotation::on(Auth::user()->database_name)->where('id',$id_quotation)
                                    ->update(['bcv' => $sin_formato_rate]);

            
            
            return redirect('/quotations/register/'.$id_quotation.'/'.$coin.'')->withSuccess('Actualizacion de Tasa Exitosa!');
        
        }

    /**
        * Remove the specified resource from storage.
        *
        * @param  int  $id
        * @return \Illuminate\Http\Response
        */
    public function deleteProduct(Request $request)
    {
        
        $quotation_product = QuotationProduct::on(Auth::user()->database_name)->find(request('id_quotation_product_modal')); 
        
        if(isset($quotation_product) && $quotation_product->status == "C"){
            QuotationProduct::on(Auth::user()->database_name)
                ->join('inventories','inventories.id','quotation_products.id_inventory')
                ->join('products','products.id','inventories.product_id')
                ->where(function ($query){
                    $query->where('products.type','MERCANCIA')
                        ->orWhere('products.type','COMBO');
                })
                ->where('quotation_products.id',$quotation_product->id)
                ->update(['inventories.amount' => DB::raw('inventories.amount+quotation_products.amount'), 'quotation_products.status' => 'X']);
        }else{
            $quotation_product->delete(); 
        }

        return redirect('/quotations/register/'.request('id_quotation_modal').'/'.request('coin_modal').'')->withDanger('Eliminacion exitosa!!');
        
    }

    public function deleteQuotation(Request $request)
    {
        
        $quotation = Quotation::on(Auth::user()->database_name)->find(request('id_quotation_modal')); 

        $global = new GlobalController();
        $global->deleteAllProducts($quotation->id);

        Anticipo::on(Auth::user()->database_name)->where('id_quotation',$quotation->id)->delete();

        $quotation->delete(); 

        return redirect('/quotations')->withDanger('Eliminacion exitosa!!');
        
    }

    public function reversar_quotation($id_quotation)
    { 
        
        $quotation = Quotation::on(Auth::user()->database_name)->findOrFail($id_quotation);

        $exist_multipayment = Multipayment::on(Auth::user()->database_name)
                            ->where('id_quotation',$quotation->id)
                            ->first();

        if(empty($exist_multipayment)){
            if($quotation != 'X'){
                $detail = DetailVoucher::on(Auth::user()->database_name)->where('id_invoice',$id_quotation)
                ->update(['status' => 'X']);
    
                
                $global = new GlobalController();
                $global->deleteAllProducts($quotation->id);

                QuotationPayment::on(Auth::user()->database_name)
                                ->where('id_quotation',$quotation->id)
                                ->update(['status' => 'X']);
    
                $quotation->status = 'X';
                $quotation->save();
            }
        }else{
            return redirect('/quotations/facturado/'.$quotation->id.'/bolivares/'.$exist_multipayment->id_header.'');
        }

        return redirect('invoices')->withSuccess('Reverso de Factura Exitosa!');

    }

    public function reversar_quotation_multipayment($id_quotation,$id_header){

        
        if(isset($id_header)){
            //aqui reversamos todo el movimiento del multipago
            DB::connection(Auth::user()->database_name)->table('detail_vouchers')
            ->join('header_vouchers', 'header_vouchers.id','=','detail_vouchers.id_header_voucher')
            ->where('header_vouchers.id','=',$id_header)
            ->update(['detail_vouchers.status' => 'X' , 'header_vouchers.status' => 'X']);

            //aqui se cambia el status de los pagos
            DB::connection(Auth::user()->database_name)->table('multipayments')
            ->join('quotation_payments', 'quotation_payments.id_quotation','=','multipayments.id_quotation')
            ->where('multipayments.id_header','=',$id_header)
            ->update(['quotation_payments.status' => 'X']);

            //aqui aumentamos el inventario y cambiamos el status de los productos que se reversaron
            DB::connection(Auth::user()->database_name)->table('multipayments')
                ->join('quotation_products', 'quotation_products.id_quotation','=','multipayments.id_quotation')
                ->join('inventories','inventories.id','quotation_products.id_inventory')
                ->join('products','products.id','inventories.product_id')
                ->where(function ($query){
                    $query->where('products.type','MERCANCIA')
                        ->orWhere('products.type','COMBO');
                })
                ->where('multipayments.id_header','=',$id_header)
                ->update(['inventories.amount' => DB::raw('inventories.amount+quotation_products.amount') ,
                        'quotation_products.status' => 'X']);
    

            //aqui le cambiamos el status a todas las facturas a X de reversado
            Multipayment::on(Auth::user()->database_name)
            ->join('quotations', 'quotations.id','=','multipayments.id_quotation')
            ->where('id_header',$id_header)->update(['quotations.status' => 'X']);

            Multipayment::on(Auth::user()->database_name)->where('id_header',$id_header)->delete();

            return redirect('invoices')->withSuccess('Reverso de Facturas Multipago Exitosa!');
        }else{
            return redirect('invoices')->withDanger('No se pudo reversar las facturas');
        }
        
    }
    

    public function listinventory(Request $request, $var = null){
        //validar si la peticion es asincrona
        if($request->ajax()){
            try{
                
                $respuesta = Inventory::on(Auth::user()->database_name)->select('id')->where('code',$var)->where('status',1)->get();
                return response()->json($respuesta,200);

            }catch(Throwable $th){
                return response()->json(false,500);
            }
        }
        
    }




}