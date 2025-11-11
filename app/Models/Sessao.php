<?php

namespace App\Models;

class Sessao extends BaseModel
{
    protected $basename = 'sessao'; // syrios_sessao

    // ✅ Agora o Laravel controla created_at e updated_at automaticamente
    public $timestamps = true;

    protected $fillable = [
        'usuario_id',
        'school_id',
        // ❌ Removido 'criado_em'
    ];

    protected $casts = [
        'created_at' => 'datetime', // ✅ novo padrão
        'updated_at' => 'datetime',
    ];

    // 🔗 Relacionamentos
    public function usuario()
    {
        return $this->belongsTo(Usuario::class, 'usuario_id');
    }

    public function escola()
    {
        return $this->belongsTo(Escola::class, 'school_id');
    }

}
