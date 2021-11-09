<?php

namespace App\Http\Controllers;

use App\Inventory;
use App\Product;
use App\Segment;
use App\Subsegment;
use App\ThreeSubsegment;
use App\TwoSubsegment;
use App\UnitOfMeasure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ProductController extends Controller
{
 
    public function __construct(){

       $this->middleware('auth');

   }

   public function index()
   {
       $user       =   auth()->user();
       $users_role =   $user->role_id;
       
        $products = Product::on(Auth::user()->database_name)->orderBy('id' ,'DESC')->where('status',1)->get();


       return view('admin.products.index',compact('products'));
   }

   /**
    * Show the form for creating a new resource.
    *
    * @return \Illuminate\Http\Response
    */
   public function create()
   {
        $segments     = Segment::on(Auth::user()->database_name)->orderBY('description','asc')->pluck('description','id')->toArray();
      
        $subsegments  = Subsegment::on(Auth::user()->database_name)->orderBY('description','asc')->get();
     
        $unitofmeasures   = UnitOfMeasure::on(Auth::user()->database_name)->orderBY('description','asc')->get();

        return view('admin.products.create',compact('segments','subsegments','unitofmeasures'));
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
            
        
            'segment'         =>'required',
            'unit_of_measure_id'         =>'required',


            'type'         =>'required',
            'description'         =>'required',
        
            'price'         =>'required',
            'price_buy'         =>'required',
            'cost_average'         =>'required',

            'money'         =>'required',
        
            'special_impuesto'         =>'required',
            
        
        ]);

        //dd($request);
        //dd(Auth::on(Auth::user()->database_name);
        $var = new Product();
        $var->setConnection(Auth::user()->database_name);

        $var->segment_id = request('segment');
        $var->subsegment_id= request('Subsegment');
        $var->unit_of_measure_id = request('unit_of_measure_id');
        $var->code_comercial = request('code_comercial');
        $var->type = request('type');
        $var->description = request('description');

        $var->twosubsegment_id= request('twoSubsegment');
        $var->threesubsegment_id= request('threeSubsegment');

        $var->id_user = request('id_user');

        $valor_sin_formato_price = str_replace(',', '.', str_replace('.', '',request('price')));
        $valor_sin_formato_price_buy = str_replace(',', '.', str_replace('.', '',request('price_buy')));
        $valor_sin_formato_cost_average = str_replace(',', '.', str_replace('.', '',request('cost_average')));
        $valor_sin_formato_special_impuesto = str_replace(',', '.', str_replace('.', '',request('special_impuesto')));
        


        $var->price = $valor_sin_formato_price;
        $var->price_buy = $valor_sin_formato_price_buy;
        $var->cost_average = $valor_sin_formato_cost_average;
        $var->money = request('money');
        $var->photo_product = request('photo_product');

        $exento = request('exento');
        if($exento == null){
            $var->exento = false;
        }else{
            $var->exento = true;
        }
        
        $islr = request('islr');
        if($islr == null){
            $var->islr = false;
        }else{
            $var->islr = true;
        }

        $var->special_impuesto = $valor_sin_formato_special_impuesto;
        $var->status =  1;
    
        $var->save();

        $inventory = new Inventory();
        $inventory->setConnection(Auth::user()->database_name);

        $inventory->product_id = $var->id;
        $inventory->id_user = $var->id_user;
        $inventory->code = $var->code_comercial;
        $inventory->amount = 0;
        $inventory->status = 1;

        $inventory->save();

        return redirect('/products')->withSuccess('Registro Exitoso!');
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
        $product = Product::on(Auth::user()->database_name)->find($id);
        $segments     = Segment::on(Auth::user()->database_name)->orderBY('description','asc')->get();
       
        $subsegments  = Subsegment::on(Auth::user()->database_name)->orderBY('description','asc')->get();

        $twosubsegments  = TwoSubsegment::on(Auth::user()->database_name)->where('subsegment_id',$product->subsegment_id)->orderBY('description','asc')->get();
     
        $threesubsegments  = ThreeSubsegment::on(Auth::user()->database_name)->where('twosubsegment_id',$product->twosubsegment_id)->orderBY('description','asc')->get();
     
        $unitofmeasures   = UnitOfMeasure::on(Auth::user()->database_name)->orderBY('description','asc')->get();

        //dd($product->subsegment_id);
       
        return view('admin.products.edit',compact('threesubsegments','twosubsegments','product','segments','subsegments','unitofmeasures'));
  
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

    $vars =  Product::on(Auth::user()->database_name)->find($id);

    $vars_status = $vars->status;
    $vars_exento = $vars->exento;
    $vars_islr = $vars->islr;
  
    $data = request()->validate([
        
       
        'segment'         =>'required',
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

    $var = Product::on(Auth::user()->database_name)->findOrFail($id);

    $var->segment_id = request('segment');
    $var->subsegment_id= request('Subsegment');
    if(request('twoSubsegment') == 'null'){
        $var->twosubsegment_id= null;
    }else{
        $var->twosubsegment_id= request('twoSubsegment');
    }

    if(request('threeSubsegment') == 'null'){
        $var->threesubsegment_id= null;
    }else{
        $var->threesubsegment_id= request('threeSubsegment');
    }
    
    
    $var->unit_of_measure_id = request('unit_of_measure_id');

    $var->code_comercial = request('code_comercial');
    $var->type = request('type');
    $var->description = request('description');

    $valor_sin_formato_price = str_replace(',', '.', str_replace('.', '',request('price')));
    $valor_sin_formato_price_buy = str_replace(',', '.', str_replace('.', '',request('price_buy')));
    $valor_sin_formato_cost_average = str_replace(',', '.', str_replace('.', '',request('cost_average')));
    $valor_sin_formato_special_impuesto = str_replace(',', '.', str_replace('.', '',request('special_impuesto')));
       


    $var->price = $valor_sin_formato_price;
    $var->price_buy = $valor_sin_formato_price_buy;
    $var->cost_average = $valor_sin_formato_cost_average;
    
    $var->photo_product = request('photo_product');

    $var->money = request('money');


    $var->special_impuesto = $valor_sin_formato_special_impuesto;

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

    return redirect('/products')->withSuccess('Actualizacion Exitosa!');
    }


   /**
    * Remove the specified resource from storage.
    *
    * @param  int  $id
    * @return \Illuminate\Http\Response
    */
   public function destroy()
   {
        $product = Product::on(Auth::user()->database_name)->find(request('id_product_modal')); 

        if(isset($product)){
            
            Inventory::on(Auth::user()->database_name)
                            ->where('product_id',$product->id)
                            ->update(['status' => 'X']);

            $product->status = 'X';

            $product->save();
    
            return redirect('/products')->withSuccess('Se ha Deshabilitado el Producto Correctamente!!');
        }
   }


}
