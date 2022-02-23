<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    protected $table = 'category';
    protected $primaryKey = 'id_category';

    const CREATED_AT = null;

    protected $casts = [
        'created_at' => 'datetime:Y-m-d H:i:s' ,
        'updated_at' => 'datetime:Y-m-d H:i:s'
    ];

    use HasFactory;

    public function producto(){
        return $this->hasOne(Producto::class, 'id_category');
    }
}
