<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Usuario extends BaseAuthModel
{
    use HasFactory;
    
    protected $basename = 'usuario';
    protected $primaryKey = 'id';

    protected $fillable = [
        'school_id',
        'cpf',
        'senha_hash',
        'nome_u',
        'status',
        // 'is_super_master' 👈 não incluímos por segurança
    ];

    protected $casts = [
        'is_super_master' => 'boolean',
    ];

    protected static function booted()
    {
        static::deleting(function ($usuario) {
            if ($usuario->is_super_master) {
                return false; // bloqueia exclusão
            }
        });
    }


    //protected $hidden = ['senha_hash'];
    protected $hidden = [
        'senha_hash', 'remember_token',
    ];

    public function professor()
    {
        return $this->hasOne(\App\Models\Professor::class, 'usuario_id');
    }


    // Laravel espera "password"
    public function getAuthPassword()
    {
        return $this->senha_hash;
    }

    public function scopeFiltrarPorEscola($query, $filtro)
    {
        if ($filtro === 'mae') {
            // usuários vinculados a escolas que são secretarias
            return $query->whereHas('escola', function($q) {
                $q->whereNull('secretaria_id');
            });
        } elseif ($filtro === 'filha') {
            // usuários vinculados a escolas que são filhas
            return $query->whereHas('escola', function($q) {
                $q->whereNotNull('secretaria_id');
            });
        }
        return $query; // todos
    }


    // um Usuário pertence a uma escola
    public function escola()
    {
        return $this->belongsTo(Escola::class, 'school_id');
    }

    // Usuário tem várias roles (multi-escola)
    public function roles()
    {   
        
         return $this->belongsToMany(
            Role::class,
            prefix('usuario_role'), // usa tabela completa com prefixo dinâmico
            'usuario_id',
            'role_id'
        )->withPivot('school_id')
         ->withTimestamps();

        // return $this->belongsToMany(
        //     Role::class, 
        //     prefix('usuario_role'), 
        //     'usuario_id', 
        //     'role_id'
        // )->withPivot('school_id');

        // return $this->belongsToMany(
        //     Role::class,
        //     'syrios_usuario_role',
        //     'usuario_id',
        //     'role_id'
        // )->withPivot('school_id');
    }

    // App/Models/Usuario.php
    public function hasRole($roleName)
    {
        return $this->roles->contains('role_name', $roleName);
    }

}
