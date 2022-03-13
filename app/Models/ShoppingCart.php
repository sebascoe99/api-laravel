<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ShoppingCart extends Model
{
    protected $table = 'shopping_cart';
    protected $primaryKey = 'id_shopping_cart';

    const CREATED_AT = null;

    protected $casts = [
        'created_at' => 'datetime:Y-m-d H:i:s' ,
        'updated_at' => 'datetime:Y-m-d H:i:s'
    ];

    public function producto(){
        return $this->belongsTo(Producto::class, 'id_product');
    }

    public function user(){
        return $this->belongsTo(User::class, 'id_user');
    }

    use HasFactory;
}
