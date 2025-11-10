<?php

namespace App\Models;

class ModeloMotivo extends BaseModel
{
    protected $basename = 'modelo_motivo';

    protected $fillable = [
        'school_id',
        'descricao',
        'categoria'
    ];

    /*
    |--------------------------------------------------------------------------
    | 🔗 RELACIONAMENTOS
    |--------------------------------------------------------------------------
    */

    public function ocorrencias()
    {
        return $this->belongsToMany(
            Ocorrencia::class,
            prefix('ocorrencia_motivo'),
            'modelo_motivo_id',
            'ocorrencia_id'
        );
    }

    /*
    |--------------------------------------------------------------------------
    | ⚙️ SCOPES
    |--------------------------------------------------------------------------
    */

    public function scopeDaEscolaAtual($query)
    {
        return $query->where('school_id', session('current_school_id'));
    }

    public function scopeAtivos($query)
    {
        return $query->where('ativo', true);
    }

    //acrescentado por mim
    public function escola()      { 
        return $this->belongsTo(Escola::class, 'school_id'); 
    }
}
