$(document).ready(function (){
    $('#myTable').DataTable(
        {
            pageLength: 50,
            "responsive": true,
            "autoWidth": false,
            language: {
                paginate: {
                    next: '>',
                    previous: '<'
                },
                search: 'Rechercher :',
                info: 'Affichage de _START_ à _END_ sur _TOTAL_ éléments',
                lengthMenu: 'Afficher _MENU_ éléments',
                emptyTable: 'Aucune donnée disponible',
                infoEmpty: 'Affichage de 0 à 0 sur 0 éléments',
                infoFiltered: '(filtré à partir de _MAX_ éléments au total)',
                loadingRecords: 'Chargement...',
                processing: 'Traitement...',
                zeroRecords: 'Aucun élément correspondant trouvé',
            },
            "order": [],
            "columnDefs": [ {
                "targets": 'no-sort',
                "orderable": false,
            } ],
        }
    );
    $('input.colorpicker-example').colorpicker({
        format: 'hex'
    });
});