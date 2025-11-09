<?php

namespace App\Models;

class Role extends BaseModel
{
    protected $basename = 'role';
    protected $primaryKey = 'id';

    protected $fillable = ['role_name'];

    public function usuarios()
    {
        return $this->belongsToMany(
            Usuario::class,
            'syrios_usuario_role',
            'role_id',
            'usuario_id'
        )->withPivot('school_id');
    }
}
