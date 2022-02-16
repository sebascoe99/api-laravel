<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class IdentificationType extends Model
{
    protected $table = 'identification_type';
    protected $primaryKey = 'id_identification_type';

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
