<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Inventary extends Model
{
    protected $table = 'inventory';
    protected $primaryKey = 'id_inventory';

    const CREATED_AT = null;

    protected $casts = [
        'created_at' => 'datetime:Y-m-d H:i:s' ,
        'updated_at' => 'datetime:Y-m-d H:i:s'
    ];

    public function producto(){
        return $this->belongsTo(Producto::class, 'id_product');
    }

    use HasFactory;
}
