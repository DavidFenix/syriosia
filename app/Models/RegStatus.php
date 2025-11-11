<?php

namespace App\Models;

class RegStatus extends BaseModel
{
    protected $basename   = 'regstatus'; // syrios_regstatus
    
    protected $fillable = [
        'id',        // PK manual (não auto-increment)
        'descr_s',
    ];

    public $incrementing = false; // PK não auto-increment
    protected $keyType   = 'int';

    public function ocorrencias() { return $this->hasMany(Ocorrencia::class, 'status_id'); }
}
