<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PurchaseOrderProducts extends Model
{
    protected $table = 'purchase_order_products';
    protected $primaryKey = 'id_purchase_order_products';

    const CREATED_AT = null;
    //const UPDATED_AT = 'update_time';
    //const UPDATED_AT = null;

    protected $casts = [
        'created_at' => 'datetime:Y-m-d H:i:s' ,
        'updated_at' => 'datetime:Y-m-d H:i:s'
    ];

    public function producto(){
        return $this->belongsTo(Producto::class, 'id_product');
    }

    public function PurchaseOrder(){
        return $this->belongsTo(PurchaseOrder::class, 'id_purchase_order');
    }

}
