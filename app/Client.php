<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Client extends Model
{

    protected $fillable = ['id','id_user','type_code','cedula_rif','name','direction','city','country','phone1'
        ,'days_credit','amount_max_credit','percentage_retencion_iva','percentage_retencion_islr','status','created_at','updated_at'];


    public function vendors(){
        return $this->belongsTo('App\Permission\Models\Vendor','id_vendor');
    }
}
