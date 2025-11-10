<?php

namespace App\Models;

class Enturmacao extends BaseModel
{
    protected $basename   = 'enturmacao'; // syrios_enturmacao
    
    // protected $fillable = [
    //     'school_id',
    //     'aluno_id',
    //     'turma_id',
    // ];

    protected $fillable = [
        'aluno_id',
        'turma_id',
        'school_id',
        'ano_letivo',
        'vigente',
    ];

    protected $casts = [
        'vigente' => 'boolean',
        'ano_letivo' => 'integer',
    ];

    public function escola() { return $this->belongsTo(Escola::class, 'school_id'); }
    public function aluno()  { return $this->belongsTo(Aluno::class, 'aluno_id'); }
    public function turma()  { return $this->belongsTo(Turma::class, 'turma_id'); }


}
