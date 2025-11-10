<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;

class Turma extends BaseModel
{
    use HasFactory;
    
    protected $basename = 'turma';

    protected $fillable = [
        'serie_turma',
        'turno',
        'school_id',
    ];

    public function escola()
    {
        return $this->belongsTo(Escola::class, 'school_id');
    }

    public function diretores()
    {
        return $this->hasMany(\App\Models\DiretorTurma::class, 'turma_id');
    }

}
