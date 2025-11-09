<?php

namespace App\Models;

class UsuarioRole extends BaseModel
{
    protected $basename = 'usuario_role';
    protected $primaryKey = null;
    public $incrementing = false;

    protected $fillable = [
        'usuario_id',
        'role_id',
        'school_id'
    ];

    public function usuario()
    {
        return $this->belongsTo(Usuario::class, 'usuario_id');
    }

    public function role()
    {
        return $this->belongsTo(Role::class, 'role_id');
    }

    public function escola()
    {
        return $this->belongsTo(Escola::class, 'school_id');
    }
}
