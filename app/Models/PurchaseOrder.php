<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PurchaseOrder extends Model
{
    protected $table = 'purchase_order';
    protected $primaryKey = 'id_purchase_order';

    const CREATED_AT = null;
    //const UPDATED_AT = 'update_time';
    //const UPDATED_AT = null;

    protected $casts = [
        'created_at' => 'datetime:Y-m-d H:i:s' ,
        'updated_at' => 'datetime:Y-m-d H:i:s'
    ];

    public function provider(){
        return $this->belongsTo(Provider::class, 'id_provider');
    }

    public function user(){
        return $this->belongsTo(User::class, 'id_user');
    }
}
