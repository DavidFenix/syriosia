{{-- Lista de Roles --}}
<div class="alert alert-info">
  Esta lista é somente para consulta. Alterações nas roles são feitas pelo desenvolvedor do sistema.
</div>

<table class="table table-bordered">
    <thead>
        <tr>
            <th>ID</th>
            <th>Nome da Role</th>
            <th>Resumo</th>
            <!--th>Ações</th-->
        </tr>
    </thead>
    <tbody>
        @foreach($roles as $role)
            <tr>
                <td>{{ $role->id }}</td>
                <td>{{ $role->role_name }}</td>
                <td>ainda não implementado</td>
            </tr>
        @endforeach
    </tbody>
</table>