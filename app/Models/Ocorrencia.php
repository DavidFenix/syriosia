<?php

namespace App\Models;

class Ocorrencia extends BaseModel
{
    protected $basename = 'ocorrencia';

    protected $fillable = [
        'school_id',
        'ano_letivo',
        'vigente',
        'aluno_id',
        'professor_id',
        'oferta_id',
        'descricao',
        'local',
        'atitude',
        'outra_atitude',
        'comportamento',
        'sugestao',
        'status',
        'nivel_gravidade',
        'sync',
        'recebido_em',
        'encaminhamentos'
    ];

    protected $casts = [
        'vigente' => 'boolean',
        'ano_letivo' => 'integer',
        'status' => 'integer',
        'nivel_gravidade' => 'integer',
        'sync' => 'integer',
        'recebido_em' => 'datetime',
    ];

    protected $guarded = ['vigente'];

    /*
    |--------------------------------------------------------------------------
    | 🔗 RELACIONAMENTOS
    |--------------------------------------------------------------------------
    */


    public function professor()
    {
        return $this->belongsTo(Professor::class, 'professor_id');
    }

    public function aluno()
    {
        return $this->belongsTo(Aluno::class, 'aluno_id');
    }

    public function oferta()
    {
        return $this->belongsTo(Oferta::class, 'oferta_id');
    }

    public function motivos()
    {
        return $this->belongsToMany(
            ModeloMotivo::class,
            prefix('ocorrencia_motivo'),
            'ocorrencia_id',
            'modelo_motivo_id'
        )->withTimestamps();
    }
    
    /*
    |--------------------------------------------------------------------------
    | ⚙️ SCOPES ÚTEIS
    |--------------------------------------------------------------------------
    */

    // Filtra ocorrências do ano letivo atual
    public function scopeAnoAtual($query)
    {
        return $query->where('ano_letivo', session('ano_letivo_atual') ?? date('Y'));
    }

    // Filtra por escola ativa
    public function scopeDaEscolaAtual($query)
    {
        return $query->where('school_id', session('current_school_id'));
    }

    // Filtra apenas ocorrências ativas
    public function scopeAtivas($query)
    {
        return $query->where('status', 1);
    }

}
