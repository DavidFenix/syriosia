<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Models\Ocorrencia;

class Aluno extends BaseModel
{   

    use HasFactory;
    
    protected $basename = 'aluno'; // vira syrios_aluno
    protected $primaryKey = 'id';

    protected $fillable = [
        'matricula',
        'school_id',
        'nome_a',
    ];

    // Relacionamento: um aluno pertence a uma escola
    public function escola()
    {
        return $this->belongsTo(Escola::class, 'school_id');
    }

    public function enturmacao()
    {
        return $this->hasMany(Enturmacao::class, 'aluno_id');
    }

    // 🏫 Enturmação atual (somente da escola logada)
    public function enturmacaoAtual()
    {
        $schoolId = session('current_school_id');
        return $this->hasOne(Enturmacao::class, 'aluno_id')
                    ->where('school_id', $schoolId)
                    ->latest();
    }

    public function turma()
    {
        return $this->belongsTo(Turma::class, 'turma_id');
    }

    public function ocorrencias()
    {
        return $this->hasMany(Ocorrencia::class, 'aluno_id');
    }

}
