<?php

namespace App\Models;

class OcorrenciaMotivo extends BaseModel
{
    protected $basename = 'ocorrencia_motivo';

    protected $fillable = [
        'ocorrencia_id',
        'modelo_motivo_id'
    ];

    public function ocorrencia()
    {
        return $this->belongsTo(Ocorrencia::class, 'ocorrencia_id');
    }

    public function motivo()
    {
        return $this->belongsTo(ModeloMotivo::class, 'modelo_motivo_id');
    }

    // OcorrenciaMotivo.php
    public function modelo() {
        return $this->belongsTo(ModeloMotivo::class, 'modelo_motivo_id');
    }

}
