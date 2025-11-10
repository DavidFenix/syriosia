/*
// =========================================================
// 👥 DataTables: Usuários
// =========================================================
$(document).ready(function() {
    const table = $('#usuariosTable').DataTable({
        responsive: true,

        // ✅ Layout do cabeçalho com busca global e controle de quantidade
        dom: '<"row mb-3"' +
                '<"col-md-4"l>' +  // seletor de quantidade
                '<"col-md-4 text-center"f>' + // 🔍 campo de pesquisa global
                '<"col-md-4 text-end"B>' +    // botões
             '>' +
             'rt' + // tabela
             '<"row mt-3"' +
                '<"col-md-5"i>' +
                '<"col-md-7"p>' +
             '>', 

        buttons: [
            { extend: 'copy', text: 'Copiar' },
            { extend: 'csv', text: 'CSV' },
            { extend: 'excel', text: 'Excel' },
            { extend: 'pdf', text: 'PDF' },
            { extend: 'print', text: 'Imprimir' }
        ],

        lengthMenu: [
            [10, 25, 50, 100, -1],
            [10, 25, 50, 100, "Todos"]
        ],

        order: [[1, 'asc']],

        language: {
            url: '//cdn.datatables.net/plug-ins/1.13.5/i18n/pt-BR.json',
            search: "🔍 Buscar:",
            lengthMenu: "Mostrar _MENU_ registros por página",
            info: "Mostrando _START_ a _END_ de _TOTAL_ registros",
            infoFiltered: "(filtrado de _MAX_ registros totais)",
            buttons: {
                copyTitle: 'Copiado!',
                copySuccess: { _: '%d linhas copiadas', 1: '1 linha copiada' }
            }
        }
    });

    // 🔍 Filtros individuais nas colunas
    $('#usuariosTable tfoot th').each(function (i) {
        const input = $(this).find('input, select');
        if (input.length) {
            $(input).on('keyup change', function () {
                if (table.column(i).search() !== this.value) {
                    table.column(i).search(this.value).draw();
                }
            });
        }
    });
});

// =========================================================
// 🏫 DataTables: Escolas e Secretarias
// =========================================================
$(document).ready(function() {
  const table2 = $('#escolasTable').DataTable({
    responsive: true,
    dom: '<"row mb-3"' +
            '<"col-md-4"l>' +
            '<"col-md-4 text-center"f>' +
            '<"col-md-4 text-end"B>' +
         '>' +
         'rt' +
         '<"row mt-3"' +
            '<"col-md-5"i>' +
            '<"col-md-7"p>' +
         '>',
    buttons: [
      { extend: 'copy', text: 'Copiar' },
      { extend: 'csv', text: 'CSV' },
      { extend: 'excel', text: 'Excel' },
      { extend: 'pdf', text: 'PDF' },
      { extend: 'print', text: 'Imprimir' }
    ],
    lengthMenu: [
      [10, 25, 50, 100, -1],
      [10, 25, 50, 100, "Todos"]
    ],
    order: [[1, 'asc']],
    language: {
      url: '//cdn.datatables.net/plug-ins/1.13.5/i18n/pt-BR.json',
      search: "🔍 Buscar:",
      lengthMenu: "Mostrar _MENU_ registros por página",
      info: "Mostrando _START_ a _END_ de _TOTAL_ registros",
      infoFiltered: "(filtrado de _MAX_ registros totais)"
    }
  });

  // 🔍 filtros individuais
  $('#escolasTable tfoot th').each(function (i) {
    const input = $(this).find('input, select');
    if (input.length) {
      $(input).on('keyup change', function () {
        if (table2.column(i).search() !== this.value) {
          table2.column(i).search(this.value).draw();
        }
      });
    }
  });
});


// =========================================================
// 🧩 DataTables: Associações Escola ↔ Filhas
// =========================================================
$(document).ready(function() {
  const table3 = $('#associacoesTable').DataTable({
    responsive: true,
    dom: '<"row mb-3"' +
            '<"col-md-4"l>' +
            '<"col-md-4 text-center"f>' +
            '<"col-md-4 text-end"B>' +
         '>' +
         'rt' +
         '<"row mt-3"' +
            '<"col-md-5"i>' +
            '<"col-md-7"p>' +
         '>',
    buttons: [
      { extend: 'copy', text: 'Copiar' },
      { extend: 'csv', text: 'CSV' },
      { extend: 'excel', text: 'Excel' },
      { extend: 'pdf', text: 'PDF' },
      { extend: 'print', text: 'Imprimir' }
    ],
    lengthMenu: [
      [10, 25, 50, 100, -1],
      [10, 25, 50, 100, "Todos"]
    ],
    order: [[1, 'asc']],
    language: {
      url: '//cdn.datatables.net/plug-ins/1.13.5/i18n/pt-BR.json',
      search: "🔍 Buscar:",
      lengthMenu: "Mostrar _MENU_ registros por página",
      info: "Mostrando _START_ a _END_ de _TOTAL_ registros",
      infoFiltered: "(filtrado de _MAX_ registros totais)"
    }
  });

  // 🔍 filtros individuais nas colunas
  $('#associacoesTable tfoot th').each(function (i) {
    const input = $(this).find('input, select');
    if (input.length) {
      $(input).on('keyup change', function () {
        if (table3.column(i).search() !== this.value) {
          table3.column(i).search(this.value).draw();
        }
      });
    }
  });
});
*/


/*
  // =========================================================
  // 🧩 DataTables: Associações Escola ↔ Filhas
  // =========================================================
  $(document).ready(function() {
    const table3 = $('#associacoesTable').DataTable({
      responsive: true,
      dom: '<"row mb-3"' +
              '<"col-md-4"l>' +
              '<"col-md-4 text-center"f>' +
              '<"col-md-4 text-end"B>' +
           '>' +
           'rt' +
           '<"row mt-3"' +
              '<"col-md-5"i>' +
              '<"col-md-7"p>' +
           '>',
      buttons: [
        { extend: 'copy', text: 'Copiar' },
        { extend: 'csv', text: 'CSV' },
        { extend: 'excel', text: 'Excel' },
        { extend: 'pdf', text: 'PDF' },
        { extend: 'print', text: 'Imprimir' }
      ],
      lengthMenu: [
        [10, 25, 50, 100, -1],
        [10, 25, 50, 100, "Todos"]
      ],
      order: [[1, 'asc']],
      language: {
        url: '//cdn.datatables.net/plug-ins/1.13.5/i18n/pt-BR.json',
        search: "🔍 Buscar:",
        lengthMenu: "Mostrar _MENU_ registros por página",
        info: "Mostrando _START_ a _END_ de _TOTAL_ registros",
        infoFiltered: "(filtrado de _MAX_ registros totais)"
      }
    });

    // 🔍 filtros individuais nas colunas
    $('#associacoesTable tfoot th').each(function (i) {
      const input = $(this).find('input, select');
      if (input.length) {
        $(input).on('keyup change', function () {
          if (table3.column(i).search() !== this.value) {
            table3.column(i).search(this.value).draw();
          }
        });
      }
    });
  });
//*/


/*
// public/js/datatables-init.js
  (function() {
    // Evita erro se jQuery/DataTables não estiverem carregados
    if (typeof $ === 'undefined' || !$.fn.DataTable) return;

    /**
     * Cria inputs de filtro no <tfoot> baseado no <thead>
     * Se o <tfoot> não existir, cria um com a mesma quantidade de colunas.
     *
    function ensureFooterFilters($table, filterableIndexes) {
      // Cria <tfoot> se não existir
      if ($table.find('tfoot').length === 0) {
        const $theadCells = $table.find('thead th');
        const $tfoot = $('<tfoot><tr></tr></tfoot>');
        $theadCells.each(function() {
          $tfoot.find('tr').append('<th></th>');
        });
        $table.append($tfoot);
      }

      // Injeta inputs nos ths do tfoot
      $table.find('tfoot th').each(function(i) {
        // Se a coluna não for filtrável, deixa vazio
        if (Array.isArray(filterableIndexes) && !filterableIndexes.includes(i)) {
          $(this).html('');
          return;
        }

        // Colunas de ações / contador normalmente não filtráveis
        const headerText = $table.find('thead th').eq(i).text().trim();
        if (!headerText || headerText === '#' || /Ações/i.test(headerText)) {
          $(this).html('');
          return;
        }

        $(this).html(
          `<input type="text" class="form-control form-control-sm" placeholder="Filtrar ${headerText}">`
        );
      });
    }

    /**
     * Inicializador genérico
     * @param {string} selector - ex: '#usuariosTable'
     * @param {object} opts - opções adicionais/override do DataTables
     * @param {number[]} filterableIndexes - índices de colunas com filtro por coluna
     *
    window.initDataTable = function(selector, opts = {}, filterableIndexes = null) {
      const $table = $(selector);
      if ($table.length === 0) return;

      // Evita reinicialização
      if ($.fn.dataTable.isDataTable(selector)) return;

      // Cria filtros no rodapé
      ensureFooterFilters($table, filterableIndexes);

      const defaultOptions = {
        responsive: true,
        dom:
          '<"row mb-3"' +
            '<"col-md-4"l>' +                    // seletor de quantidade
            '<"col-md-4 text-center"f>' +        // busca global
            '<"col-md-4 text-end"B>' +           // botões
          '>' +
          'rt' +                                  // tabela
          '<"row mt-3"' +
            '<"col-md-5"i>' +                    // info
            '<"col-md-7"p>' +                    // paginação
          '>',
        buttons: [
          { extend: 'pageLength', text: 'Linhas' },
          { extend: 'copy', text: 'Copiar' },
          { extend: 'csv', text: 'CSV' },
          { extend: 'excel', text: 'Excel' },
          { extend: 'pdf', text: 'PDF' },
          { extend: 'print', text: 'Imprimir' }
        ],
        lengthMenu: [
          [5, 10, 25, 50, 100, -1],
          [5, 10, 25, 50, 100, 'Todos']
        ],
        order: [[1, 'asc']],
        language: {
          url: 'https://cdn.datatables.net/plug-ins/1.13.8/i18n/pt-BR.json',
          search: '🔍 Buscar:',
          lengthMenu: 'Mostrar _MENU_ registros por página',
          info: 'Mostrando _START_ a _END_ de _TOTAL_ registros',
          infoFiltered: '(filtrado de _MAX_ no total)',
          buttons: {
            copyTitle: 'Copiado!',
            copySuccess: { _: '%d linhas copiadas', 1: '1 linha copiada' }
          }
        }
      };

      const table = $table.DataTable($.extend(true, {}, defaultOptions, opts));

      // Liga os filtros individuais
      table.columns().every(function(i) {
        const col = this;
        const input = $table.find('tfoot th').eq(i).find('input, select');
        if (input.length) {
          $(input).on('keyup change clear', function() {
            if (col.search() !== this.value) {
              col.search(this.value).draw();
            }
          });
        }
      });

      return table;
    };

    // =============== Inicializações automáticas por ID padrão ===============

    // 👥 Tabela de Usuários (se existir)
    if ($('#usuariosTable').length) {
      // Ex.: filtrar por todas as colunas, exceto contador (#) e ações
      const totalCols = $('#usuariosTable thead th').length;
      const filterable = [...Array(totalCols).keys()].filter(i => i !== 0 && i !== totalCols - 1);
      initDataTable('#usuariosTable', { order: [[1, 'asc']] }, filterable);
    }

    // 🏫 Tabela de Escolas (se existir)
    if ($('#tabela-escolas').length) {
      // Colunas: [#, Nome, Cidade, Data, INEP, CNPJ, Ações]
      // Filtrar: Nome, Cidade, Data, INEP, CNPJ (índices 1..5)
      initDataTable('#tabela-escolas', { order: [[1, 'asc']] }, [1,2,3,4,5]);
    }
  })();

*/


// =========================================================
// 📊 Função genérica para DataTables (unificada e segura)
// =========================================================
(function() {
  if (typeof $ === 'undefined' || !$.fn.DataTable) return;

  // 🔹 Cria filtros individuais no rodapé
  function ensureFooterFilters($table, filterableIndexes) {
    if ($table.find('tfoot').length === 0) {
      const $theadCells = $table.find('thead th');
      const $tfoot = $('<tfoot><tr></tr></tfoot>');
      $theadCells.each(() => $tfoot.find('tr').append('<th></th>'));
      $table.append($tfoot);
    }

    $table.find('tfoot th').each(function(i) {
      const headerText = $table.find('thead th').eq(i).text().trim();
      if (
        (Array.isArray(filterableIndexes) && !filterableIndexes.includes(i)) ||
        !headerText || headerText === '#' || /Ações/i.test(headerText)
      ) {
        $(this).html('');
      } else {
        $(this).html(`<input type="text" class="form-control form-control-sm" placeholder="Filtrar ${headerText}">`);
      }
    });
  }

  // 🔹 Inicializador genérico reutilizável
  window.initDataTable = function(selector, opts = {}, filterableIndexes = null) {
    $.fn.dataTable.ext.errMode = 'none';
    const $table = $(selector);
    if ($table.length === 0 || $.fn.dataTable.isDataTable(selector)) return;

    ensureFooterFilters($table, filterableIndexes);

    const defaultOptions = {
      responsive: true,
      pageLength: 10, // 👈 número inicial de registros por página
      dom:
        '<"row mb-3"' +
          '<"col-md-4"l>' +
          '<"col-md-4 text-center"f>' +
          '<"col-md-4 text-end"B>' +
        '>' +
        'rt' +
        '<"row mt-3"' +
          '<"col-md-5"i>' +
          '<"col-md-7"p>' +
        '>',
      buttons: [
        //{ extend: 'pageLength', text: 'Linhas' },
        { extend: 'copy', text: 'Copiar' },
        { extend: 'csv', text: 'CSV' },
        { extend: 'excel', text: 'Excel' },
        { extend: 'pdf', text: 'PDF' },
        { extend: 'print', text: 'Imprimir' }
      ],
      lengthMenu: [[5, 10, 25, 50, 100, -1], [5, 10, 25, 50, 100, 'Todos']],
      order: [[1, 'asc']],
      language: {
        url: 'https://cdn.datatables.net/plug-ins/1.13.8/i18n/pt-BR.json',
        search: '🔍 Buscar:',
        lengthMenu: 'Mostrar _MENU_ registros por página',
        info: 'Mostrando _START_ a _END_ de _TOTAL_ registros',
        infoFiltered: '(filtrado de _MAX_ no total)'
      }
    };

    const table = $table.DataTable($.extend(true, {}, defaultOptions, opts));

    table.columns().every(function(i) {
      const input = $table.find('tfoot th').eq(i).find('input');
      if (input.length) {
        $(input).on('keyup change', () => {
          if (this.search() !== input.val()) this.search(input.val()).draw();
        });
      }
    });

    return table;
  };

  // 🔹 Inicializações automáticas padrão
  if ($('#usuariosTable').length) {
    const totalCols = $('#usuariosTable thead th').length;
    const filterable = [...Array(totalCols).keys()].filter(i => i !== 0 && i !== totalCols - 1);
    initDataTable('#usuariosTable', { order: [[1, 'asc']] }, filterable);
  }

  if ($('#tabela-escolas').length) {
    initDataTable('#tabela-escolas', { order: [[1, 'asc']] }, [1, 2, 3, 4, 5]);
  }

  if ($('#associacoesTable').length) {
    const totalCols = $('#associacoesTable thead th').length;
    const filterable = [...Array(totalCols).keys()].filter(i => i !== 0 && i !== totalCols - 1);
    initDataTable('#associacoesTable', { order: [[1, 'asc']] }, filterable);
  }

})();
