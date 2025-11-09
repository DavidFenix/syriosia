<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;

class Professor extends BaseModel
{
    use HasFactory;
    
    protected $basename = 'professor';

    protected $primaryKey = 'id';

    protected $fillable = [
        'usuario_id',
        'school_id',
    ];

    public function usuario()
    {
        return $this->belongsTo(Usuario::class, 'usuario_id');
    }


    public function escola()
    {
        return $this->belongsTo(Escola::class, 'school_id');
    }
}

