<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ExpensesDetail extends Model
{
    public function inventories(){
        return $this->belongsTo('App\Permission\Models\Inventory','id_inventory');
    }

    public function expenses(){
        return $this->belongsTo('App\ExpensesAndPurchase','id_expense');
    }
}
