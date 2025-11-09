<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BaseModel extends Model
{
    
    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);

        // se a classe define $basename, aplica o prefixo do config
        if (!isset($this->table) && property_exists($this, 'basename')) {
            $prefix = config('prefix.tabelas', 'syrios_');
            $this->table = $prefix . $this->basename;
        }
    }

    // ✅ Accessor global - data de criação formatada em pt-BR
    public function getCreatedAtBrAttribute()
    {
        return $this->created_at ? $this->created_at->format('d/m/Y H:i') : null;
    }

    // ✅ Accessor global - data de atualização formatada em pt-BR
    public function getUpdatedAtBrAttribute()
    {
        return $this->updated_at ? $this->updated_at->format('d/m/Y H:i') : null;
    }

}