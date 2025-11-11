<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="utf-8">
    <title>Histórico de Ocorrências</title>

    <style>
        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 12px;
            color: #000;
        }

        .cabecalho {
            text-align: center;
            border-bottom: 2px solid #000;
            padding-bottom: 6px;
            margin-bottom: 10px;
        }

        .cabecalho img {
            width: 60px;
            height: 60px;
            object-fit: contain;
            border-radius: 6px;
        }

        .dados-aluno {
            border: 1px solid #999;
            border-radius: 8px;
            padding: 10px;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
        }

        .dados-aluno img {
            width: 70px;
            height: 70px;
            border-radius: 50%;
            margin-right: 10px;
            object-fit: cover;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }

        th, td {
            border: 1px solid #444;
            padding: 6px;
            text-align: left;
        }

        th {
            background: #eee;
        }
    </style>
</head>
<body>

    @php
        /*
        |--------------------------------------------------------------------------
        | ✅ LOGO DA ESCOLA — APENAS VIA asset() (URL pública)
        |--------------------------------------------------------------------------
        */
        $logoRel = ($escola && $escola->logo_path)
            ? "storage/{$escola->logo_path}"
            : "storage/img-user/padrao.png";

        $logoUrl = asset($logoRel);
    @endphp

    <div class="cabecalho">
        <img src="{{ $logoUrl }}" alt="Logo">
        <h2 style="margin:0;padding:0;">{{ $escola->nome_e ?? 'Escola' }}</h2>
        @if(!empty($escola->frase_efeito))
            <small>“{{ $escola->frase_efeito }}”</small>
        @endif
    </div>


    @php
        /*
        |--------------------------------------------------------------------------
        | ✅ FOTO DO ALUNO — via URL pública
        |--------------------------------------------------------------------------
        */
        $fotoRel = "storage/img-user/{$aluno->matricula}.png";

        if (!file_exists(public_path($fotoRel))) {
            $fotoRel = "storage/img-user/padrao.png";
        }

        $fotoFinal = asset($fotoRel);
    @endphp


    <div class="dados-aluno">
        <img src="{{ $fotoFinal }}" alt="Foto do aluno">
        <div>
            <div><strong>Aluno:</strong> {{ $aluno->nome_a }}</div>
            <div><strong>Matrícula:</strong> {{ $aluno->matricula }}</div>
            <div><strong>Turma:</strong> {{ $aluno->turma->serie_turma ?? '-' }}</div>
        </div>
    </div>


    <h3 style="text-align:center;">Histórico de Ocorrências</h3>

    <table style="text-align:center;">
        <thead>
            <tr>
                <th>#</th>
                <th>Data</th>
                <th>Descrição / Motivos</th>
                <th>Disciplina</th>
                <th>Professor</th>
                <th>Status</th>
            </tr>
        </thead>

        <tbody>
            @foreach($ocorrencias as $i => $o)
                @php
                    $nome = $o->professor->usuario->nome_u ?? '';
                    $partes = explode(' ', trim($nome));
                    $primeiro = $partes[0] ?? '';
                    $ultimo = count($partes) > 1 ? end($partes) : '';
                @endphp

                <tr>
                    <td>{{ $i + 1 }}</td>
                    <td>{{ $o->created_at->format('d/m/Y') }}</td>
                    <td>
                        {{ $o->descricao }}
                        @if($o->motivos->isNotEmpty())
                            / {{ $o->motivos->pluck('descricao')->implode(' / ') }}
                        @endif
                    </td>
                    <td>{{ $o->oferta->disciplina->abr ?? '-' }}</td>
                    <td>{{ $primeiro }} {{ $ultimo }}</td>
                    <td>{{ $o->status == 1 ? 'Ativa' : 'Arquivada' }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

</body>
</html>
