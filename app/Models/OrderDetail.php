<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OrderDetail extends Model
{
    protected $table = 'order_detail';
    protected $primaryKey = 'id_order_detail';

    const CREATED_AT = null;

    protected $casts = [
        'created_at' => 'datetime:Y-m-d H:i:s' ,
        'updated_at' => 'datetime:Y-m-d H:i:s'
    ];

    public function producto(){
        return $this->belongsTo(Producto::class, 'id_product');
    }

    public function typePay(){
        return $this->belongsTo(TypePay::class, 'id_pay');
    }

    use HasFactory;
}
