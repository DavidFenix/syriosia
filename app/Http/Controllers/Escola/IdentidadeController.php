<?php

namespace App\Http\Controllers\Escola;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Escola;
use Illuminate\Support\Facades\Storage;

class IdentidadeController extends Controller
{
    public function edit()
    {
        $schoolId = session('current_school_id');
        $escola = Escola::findOrFail($schoolId);
        return view('escola.identidade.edit', compact('escola'));
    }

    public function update(Request $request)
    {
        $schoolId = session('current_school_id');
        $escola = Escola::findOrFail($schoolId);

        $request->validate([
            'frase_efeito' => 'nullable|string|max:255',
            'logo' => 'nullable|image|max:2048', // até 2 MB
        ]);

        $escola->frase_efeito = $request->frase_efeito;

        if ($request->hasFile('logo')) {
            // Apaga o logo antigo se existir
            if ($escola->logo_path && Storage::disk('public')->exists($escola->logo_path)) {
                Storage::disk('public')->delete($escola->logo_path);
            }

            // Salva novo logo
            $path = $request->file('logo')->store('logos', 'public');
            $escola->logo_path = $path;
        }

        $escola->save();

        return redirect()
            ->route('escola.identidade.edit')
            ->with('success', '✅ Identidade visual atualizada com sucesso!');
        
    }
}

