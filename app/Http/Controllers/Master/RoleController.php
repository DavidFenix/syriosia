<?php

namespace App\Http\Controllers\Master;

use App\Http\Controllers\Controller;
use App\Models\Role;
use Illuminate\Http\Request;

class RoleController extends Controller
{
    
    public function index()
    {
        $roles = Role::all();
        return view('master.roles.index', compact('roles'));
    }

    public function create() {
        abort(403, 'Criação de roles não permitida.');
    }

    public function store(Request $request) {
        abort(403, 'Criação de roles não permitida.');
    }

    public function edit(Role $role) {
        abort(403, 'Edição de roles não permitida.');
    }

    public function update(Request $request, Role $role) {
        abort(403, 'Edição de roles não permitida.');
    }

    public function destroy(Role $role) {
        abort(403, 'Exclusão de roles não permitida.');
    }

}

