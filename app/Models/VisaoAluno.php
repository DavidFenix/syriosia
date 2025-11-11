<?php

namespace App\Models;

class VisaoAluno extends BaseModel
{
    protected $basename   = 'visao_aluno'; // syrios_visao_aluno
    
    protected $fillable = [
        'aluno_id',
        'dat_ult_visao',
        'school_id',
    ];

    protected $casts = [
        'dat_ult_visao' => 'datetime',
    ];

    public function aluno()  { return $this->belongsTo(Aluno::class, 'aluno_id'); }
    public function escola() { return $this->belongsTo(Escola::class, 'school_id'); }
}
