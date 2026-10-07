$(document).ready(function (){
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

    // Delete Type Project
    let deleteId = 0;
    $('.btn-delete').on('click', function () {
        deleteId = $(this).data('id');
    });
    $('.btn-confirm-delete').on('click', function () {
        if (deleteId > 0) {
            deleteTypeProject(deleteId);
        }
    });

    $('#editTypeModal').on('show.bs.modal', function (event) {
        $('#editTypeModal .modal-body').find('input,textarea,select').val('').end()
            .find('i').css('background-color', '#1B5A2E');
        var button  = $(event.relatedTarget);
        var modal   = $(this);
        var type_id = button.data('id') ?? 0;
        var label   = button.data('label') ?? '';
        var color   = button.data('color') ?? '#1B5A2E';

        modal.find('.modal-body #id').val(type_id);
        modal.find('.modal-body #label').val(label);
        modal.find('.modal-body #color').val(color);   
        modal.find('.modal-body i').css('background-color', color);
    });
});

function deleteTypeProject(id) {
    $.ajax({
        url: url_delete,
        method: 'DELETE',
        data: { id: id },
        success: function (response) {
            console.log(response.success);
            if (response.success === true) {
                toastr.success( response.message ?? 'Suppression effectuée avec succès');
                setTimeout(() => {
                    location.reload();
                }, 2200);
            } else {
                toastr.error( response.message ?? 'Erreur lors de la suppression');
            }
        },
        error: function () {
            //alert('Erreur lors de la suppression.');
            toastr.error('Erreur lors de la suppression');
        },
        complete: function () {
            $('#deleteTypeModal').modal('hide');
        }
    });
}