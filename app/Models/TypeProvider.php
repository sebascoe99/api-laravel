<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Http\Controllers\Provider;

class TypeProvider extends Model
{
    protected $table = 'type_provider';
    protected $primaryKey = 'id_type_provider';

    const CREATED_AT = null;

    protected $casts = [
        'created_at' => 'datetime:Y-m-d H:i:s' ,
        'updated_at' => 'datetime:Y-m-d H:i:s'
    ];

    use HasFactory;

    public function provider(){
        return $this->belongsTo(Provider::class);
    }
}
