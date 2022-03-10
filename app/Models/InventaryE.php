<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InventaryE extends Model
{
    protected $table = 'inventory_e';
    protected $primaryKey = 'id_inventory_e';

    const CREATED_AT = null;

    protected $casts = [
        'created_at' => 'datetime:Y-m-d H:i:s' ,
        'updated_at' => 'datetime:Y-m-d H:i:s'
    ];

    public function order(){
        return $this->belongsTo(Order::class, 'id_order');
    }

    use HasFactory;
}
