<?php

namespace App\Models;

use App\Http\Controllers\TypeProviderController;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Provider extends Model
{
    protected $table = 'provider';
    protected $primaryKey = 'id_provider';

    const CREATED_AT = null;

    protected $casts = [
        'created_at' => 'datetime:Y-m-d H:i:s' ,
        'updated_at' => 'datetime:Y-m-d H:i:s'
    ];

    use HasFactory;

    public function type_provider(){
        return $this->hasOne(TypeProvider::class, 'id_type_provider');
    }

    public function identification_type(){
        return $this->hasOne(IdentificationType::class, 'id_identification_type');
    }
}
