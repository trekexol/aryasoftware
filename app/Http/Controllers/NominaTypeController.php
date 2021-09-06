<?php

namespace App\Http\Controllers;

use App\NominaType;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class NominaTypeController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function index()
    {
        
        $user       =   auth()->user();
        $users_role =   $user->role_id;
        if($users_role == '1'){
           $nominatypes =NominaType::on(Auth::user()->database_name)->orderBy('id', 'asc')->get();
        }elseif($users_role == '2'){
            return view('admin.index');
        }

    
        return view('admin.nominatypes.index',compact('nominatypes'));
      
    }

    public function create()
    {

        

        return view('admin.nominatypes.create');
    }

    public function store(Request $request)
    {
        
        $data = request()->validate([
           
            'description'         =>'required|max:255',
            'status'         =>'required|max:2',
            
           
        ]);

        $users = new Nominatype();
        $users->setConnection(Auth::user()->database_name);

        $users->description = request('description');
        $users->status =  request('status');
       

        $users->save();

        return redirect('nominatypes')->withSuccess('Registro Exitoso!');
    }



    public function edit($id)
    {

        $var = Nominatype::on(Auth::user()->database_name)->find($id);
        
        return view('admin.nominatypes.edit',compact('var'));
    }

   


    public function update(Request $request,$id)
    {
       
        $vars =  Nominatype::on(Auth::user()->database_name)->find($id);

        $var_status = $vars->status;
      

        $request->validate([
        
            'description'      =>'required|string|max:255',
            'status'     =>'max:2',
        ]);

        

        $var  = Nominatype::on(Auth::user()->database_name)->findOrFail($id);
        $var->description        = request('description');
       
        if(request('status') == null){
            $var->status = $var_status;
        }else{
            $var->status = request('status');
        }
       

        $var->save();


        return redirect('nominatypes')->withSuccess('Registro Guardado Exitoso!');

    }


}
