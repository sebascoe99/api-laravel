<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TypePay extends Model
{
    protected $table = 'type_pay';
    protected $primaryKey = 'id_pay';

    const CREATED_AT = null;

    protected $casts = [
        'created_at' => 'datetime:Y-m-d H:i:s' ,
        'updated_at' => 'datetime:Y-m-d H:i:s'
    ];

    public function orderDetail(){
        return $this->hasOne(OrderDetail::class, 'id_pay');
    }

    use HasFactory;
}
