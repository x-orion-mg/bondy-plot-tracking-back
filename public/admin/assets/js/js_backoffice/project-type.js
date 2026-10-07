$(document).ready(function (){
    initTable();
    $('.colorpicker-example').colorpicker({
        format: 'hex',
        colorSelectors: {
            'marron': '#C0965C',
            'vert': '#1B5A2E',
            'red': '#FF0000',
            'default': '#777777',
            'primary': '#337ab7',
            'success': '#5cb85c',
            'info': '#5bc0de',
            'warning': '#f0ad4e',
            'danger': '#d9534f'
        }
    });

    $('a.btn-edit').on('click', function () {
        const id = $(this).data('id');
        const label = $(this).data('label');
        const color = $(this).data('color');

        $('input[name="id"]').val(id);
        $('input[name="label"]').val(label);
        $('input[name="color"]').val(color);    
    });

    $('a.btn-delete').on('click', function () {
        const id = $(this).data('id');
        $('input[name="id"]').val(id);
    });
});

function initTable() {
    $('#myTable').DataTable(
        {
            pageLength: 10,
            "responsive": true,
            "autoWidth": false,
            language: {
                paginate: {
                    next: '>',
                    previous: '<'
                },
                search : 'Rechercher',
                "info":  "_START_ à _END_ sur _TOTAL_ éléments",
                "lengthMenu":  "Afficher _MENU_ éléments",
                "emptyTable":     "Pas de données",
            },
            "order": [],
            "columnDefs": [ {
                "targets": 'no-sort',
                "orderable": false,
            } ],
        }
    );
}