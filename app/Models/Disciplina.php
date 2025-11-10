<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;

class Disciplina extends BaseModel
{
    use HasFactory;
    
    protected $basename = 'disciplina';

    protected $fillable = [
        'abr',
        'descr_d',
        'school_id',
    ];

    public function escola()
    {
        return $this->belongsTo(Escola::class, 'school_id');
    }
}
