<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Producto extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'product';
    protected $primaryKey = 'id_product';

    const CREATED_AT = null;
    //const UPDATED_AT = 'update_time';
    //const UPDATED_AT = null;

    protected $casts = [
        'created_at' => 'datetime:Y-m-d H:i:s' ,
        'updated_at' => 'datetime:Y-m-d H:i:s'
    ];

    public function brand(){
        return $this->belongsTo(Brand::class, 'id_brand');
    }

    public function category(){
        return $this->belongsTo(Category::class, 'id_category');
    }

    public function user(){
        return $this->belongsTo(User::class, 'id_user');
    }

    public function productUnit(){
        return $this->belongsTo(ProductoUnit::class, 'id_product_unit');
    }

    public function promotion(){
        return $this->hasOne(Promotion::class, 'id_product');
    }

    public function inventaryI(){
        return $this->hasOne(InventaryI::class, 'id_product');
    }

    use HasFactory;
    /*protected $fillable = ['product_code','product_description', 'product_stock_minimum', 'product_price',
                            'product_image', 'product_status', 'product_rating'];*/
}
