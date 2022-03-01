<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Audit extends Model
{
     /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'audit';
    protected $primaryKey = 'id_audit';

    const CREATED_AT = null;
    //const UPDATED_AT = 'update_time';
    //const UPDATED_AT = null;

    protected $casts = [
        'created_at' => 'datetime:Y-m-d H:i:s' ,
        'updated_at' => 'datetime:Y-m-d H:i:s'
    ];

    use HasFactory;

    public function user(){
        return $this->belongsTo(User::class, 'id_user');
    }
}
