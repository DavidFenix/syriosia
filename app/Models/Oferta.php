<?php

namespace App\Models;

class Oferta extends BaseModel
{
    protected $basename = 'oferta';

    protected $fillable = [
        'school_id',
        'professor_id',
        'disciplina_id',
        'turma_id',
        'ano_letivo',
        'vigente',
        'status',
    ];


    protected $casts = [
        'vigente' => 'boolean',
        'ano_letivo' => 'integer',
    ];

    public function disciplina()
    {
        return $this->belongsTo(Disciplina::class, 'disciplina_id');
    }

    public function turma()
    {
        return $this->belongsTo(Turma::class, 'turma_id');
    }

    public function professor()
    {
        return $this->belongsTo(Professor::class, 'professor_id');
    }
}
