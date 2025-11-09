@php
    header('Content-Type: text/html; charset=utf-8');
@endphp
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
            width: 50px;
            height: 50px;
            border-radius: 50%;
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

        @media print {
            .no-print {
                display: none !important;
            }
        }

    </style>
</head>
<body>

    @php
        $fotoRel_u = 'storage/img-user/logo1_ubiratan.png';
        $fotoAbsoluto_u = public_path($fotoRel_u);

        if (!file_exists($fotoAbsoluto_u)) {
            $fotoAbsoluto_u = public_path('storage/img-user/logo1_ubiratan.png');
        }
    @endphp

    {{--@include('components.pdf_header')--}}

    <div class="cabecalho">
        <img src="{{ public_path('storage/img-user/logo1_ubiratan.png') }}" alt="Logo" style="margin:1;padding:1;">
        <h2 style="margin:0;padding:0;">{{ $escola->nome_e ?? 'Escola' }}</h2>
        <small style="margin:1;padding:1;">“Educar é transformar o mundo.”</small>
    </div>

    @php
        $fotoRel = 'storage/img-user/' . $aluno->matricula . '.png';
        $fotoAbsoluto = public_path($fotoRel);

        if (!file_exists($fotoAbsoluto)) {
            $fotoAbsoluto = public_path('storage/img-user/padrao.png');
        }
    @endphp

    @php
        $fotoPath = public_path("storage/img-user/{$aluno->matricula}.png");
        $fotoFinal = file_exists($fotoPath)
            ? "file://{$fotoPath}"
            : "file://" . public_path('storage/img-user/padrao.png');
    @endphp

    <table width="100%" cellpadding="0" cellspacing="0" 
            style="
              margin-bottom:14px;
              border: 1px solid #333; 
              border-radius: 8px; 
              border-collapse: separate; 
              border-spacing: 0; 
              margin-bottom: 14px;">
      <tr>
        <td width="40" valign="middle" align="center" style="padding-right:2px;border:none;">
          <img src="{{ $fotoFinal }}" alt="Foto"
               style="padding:0px; margin: 1px; width:70px;height:70px;border-radius:50%;object-fit:cover;">
        </td>
        <td valign="middle" align="left" style="line-height:1.4;border:none;">
          <div><strong>Aluno:</strong> {{ $aluno->nome_a }}</div>
          <div><strong>Matrícula:</strong> {{ $aluno->matricula }}</div>
          <div><strong>Turma:</strong> {{ $aluno->turma->serie_turma ?? '-' }}</div>
        </td>
      </tr>
    </table>



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
                    <td style="text-align:center;">{{ $i + 1 }}</td>
                    <td style="text-align:center;">{{ $o->created_at->format('d/m/Y') }}</td>
                    <td style="text-align:center;">
                        {{ $o->descricao }}
                        @if($o->motivos->isNotEmpty())
                            / {{ $o->motivos->pluck('descricao')->implode(' / ') }}
                        @endif
                    </td>
                    <td style="text-align:center;">{{ $o->oferta->disciplina->abr ?? '-' }}</td>
                    <td style="text-align:center;">{{ $primeiro }} {{ $ultimo }}</td>
                    <td style="text-align:center;">{{ $o->status == 1 ? 'Ativa' : 'Arquivada' }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

</body>
</html>
