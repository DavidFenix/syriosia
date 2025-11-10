<?php

namespace App\Models;

class DiretorTurma extends BaseModel
{
    protected $basename = 'diretor_turma';

    protected $fillable = [
        'professor_id',
        'turma_id',
        'school_id',
        'ano_letivo',
        'vigente',
    ];

    protected $casts = [
        'vigente' => 'boolean',
        'ano_letivo' => 'integer',
    ];
    
    public function usuario()
    {
        return $this->belongsTo(Usuario::class, 'usuario_id');
    }

    public function turma()
    {
        return $this->belongsTo(Turma::class, 'turma_id');
    }

    public function escola()
    {
        return $this->belongsTo(Escola::class, 'school_id');
    }

    public function professor()
    {
        return $this->belongsTo(Professor::class, 'professor_id');
    }

}
