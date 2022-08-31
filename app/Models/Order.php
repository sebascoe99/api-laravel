<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    protected $table = 'order';
    protected $primaryKey = 'id_order';

    const CREATED_AT = null;

    protected $casts = [
        'created_at' => 'datetime:Y-m-d H:i:s' ,
        'updated_at' => 'datetime:Y-m-d H:i:s'
    ];

    public function inventaryE(){
        return $this->hasOne(InventaryE::class, 'id_order');
    }

    public function orderStatus(){
        return $this->belongsTo(OrderStatus::class, 'id_order_status');
    }

    public function user(){
        return $this->belongsTo(User::class, 'id_user');
    }

    public function orderOrderDatail(){
        return $this->hasOne(OrderOrderDetail::class, 'id_order');
    }

    public function typePay(){
        return $this->belongsTo(TypePay::class, 'id_pay');
    }

    use HasFactory;
}
