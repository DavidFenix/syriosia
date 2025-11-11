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
            margin-bottom: 15px;
            padding-bottom: 8px;
        }

        .cabecalho img {
            width: 60px;
            height: 60px;
            object-fit: contain;
            border-radius: 6px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 8px;
        }

        th, td {
            border: 1px solid #333;
            padding: 6px;
        }

        th {
            background: #eee;
        }

        .dados-aluno img {
            width: 70px;
            height: 70px;
            border-radius: 50%;
            object-fit: cover;
        }
    </style>
</head>

<body>

    {{-- ===================== CABEÇALHO ===================== --}}
    <div class="cabecalho">
        @if($logoBase64)
            <img src="{{ $logoBase64 }}" alt="Logo">
        @endif

        <h2 style="margin: 4px 0 0 0;">
            {{ $escola->nome_e ?? 'Escola' }}
        </h2>

        @if(!empty($escola->frase_efeito))
            <div style="font-size: 10px; margin-top: -2px;">
                <em>"{{ $escola->frase_efeito }}"</em>
            </div>
        @endif
    </div>

    {{-- ===================== ALUNO ===================== --}}
    <table class="dados-aluno" style="margin-bottom:10px;">
        <tr>
            <td width="80" align="center" style="border:none;">
                @if($fotoAlunoBase64)
                    <img src="{{ $fotoAlunoBase64 }}" alt="Foto">
                @endif
            </td>

            <td style="border:none;">
                <strong>Aluno:</strong> {{ $aluno->nome_a }}<br>
                <strong>Matrícula:</strong> {{ $aluno->matricula }}<br>
                <strong>Turma:</strong> {{ $turma->serie_turma ?? '-' }}
            </td>
        </tr>
    </table>

    {{-- ===================== TÍTULO ===================== --}}
    <h3 style="text-align:center; margin-top:5px;">Histórico de Ocorrências</h3>

    {{-- ===================== TABELA ===================== --}}
    <table>
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
                    <td align="center">{{ $i+1 }}</td>
                    <td align="center">{{ $o->created_at->format('d/m/Y') }}</td>
                    <td>
                        {{ $o->descricao }}
                        @if($o->motivos->isNotEmpty())
                            / {{ $o->motivos->pluck('descricao')->implode(' / ') }}
                        @endif
                    </td>
                    <td align="center">{{ $o->oferta->disciplina->abr ?? '-' }}</td>
                    <td align="center">{{ $primeiro }} {{ $ultimo }}</td>
                    <td align="center">{{ $o->status ? 'Ativa' : 'Arquivada' }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

</body>
</html>
