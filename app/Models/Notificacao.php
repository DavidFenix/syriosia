<?php

namespace App\Models;

class Notificacao extends BaseModel
{
    protected $basename = 'notificacao';

    protected $fillable = [
        'usuario_id',
        'mensagem',
        'lida',
        'school_id',
    ];

    public function usuario()
    {
        return $this->belongsTo(Usuario::class, 'usuario_id');
    }
}
