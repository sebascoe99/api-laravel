<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProductoUnit extends Model
{
    use HasFactory;

    protected $table = 'product_unit';
    protected $primaryKey = 'id_product_unit';

    const CREATED_AT = null;

    protected $casts = [
        'created_at' => 'datetime:Y-m-d H:i:s' ,
        'updated_at' => 'datetime:Y-m-d H:i:s'
    ];

    public function producto(){
        return $this->hasOne(Producto::class, 'id_product_unit');
    }
    
}
