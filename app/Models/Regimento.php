<?php

namespace App\Models;

class Regimento extends BaseModel
{
    protected $basename = 'regimento';

    protected $fillable = [
        'school_id',
        'titulo',
        'arquivo',
    ];

    public function escola()
    {
        return $this->belongsTo(Escola::class, 'school_id');
    }
}
