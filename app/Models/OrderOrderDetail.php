<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OrderOrderDetail extends Model
{
    protected $table = 'order_order_detail';
    protected $primaryKey = 'id_order_order_detail';

    const CREATED_AT = null;

    protected $casts = [
        'created_at' => 'datetime:Y-m-d H:i:s' ,
        'updated_at' => 'datetime:Y-m-d H:i:s'
    ];

    public function order(){
        return $this->belongsTo(Order::class, 'id_order');
    }

    public function orderDetail(){
        return $this->belongsTo(OrderDetail::class, 'id_order_detail');
    }

    use HasFactory;
}
