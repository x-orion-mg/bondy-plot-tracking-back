$(document).ready(function (){
    var data_table = $('#myTable').DataTable(
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
    $('.select2-example').select2({
        placeholder: 'Select'
    });

    $(document).on('change','#project-type', function() {
        var typeId = $('#project-type').find(":selected").val();
        console.log('ici', typeId);
        data_table.draw();
    });
});