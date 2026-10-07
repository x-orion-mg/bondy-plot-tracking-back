$(document).ready(function (){
    $('.select2-example').select2({
        placeholder: 'Selectionner'
    });

 
    $('input.datepick').daterangepicker({
        singleDatePicker: true,
        showDropdowns: true,
        locale: {
            format: 'DD/MM/YYYY',
            applyLabel: "Appliquer",
            cancelLabel: "Annuler",
            daysOfWeek: ["Dim", "Lun", "Mar", "Mer", "Jeu", "Ven", "Sam"],
            monthNames: [
                "Janvier", "Février", "Mars", "Avril", "Mai", "Juin",
                "Juillet", "Août", "Septembre", "Octobre", "Novembre", "Décembre"
            ],
            firstDay: 1
        }
    });

    $('.app-sidebar-menu').fixTo('body', {
        zIndex: 1,
        top: 75
    });

    let magnificPopupGalleryConfig = {
        type: 'image',
        gallery: {
            enabled: true
        }
    };
    $('.image-popup-gallery-item').magnificPopup(magnificPopupGalleryConfig);

    $('.app-sidebar-menu .list-group >a').on('click', function(){
        $('.app-sidebar-menu .list-group >a').removeClass('active');
        $(this).addClass('active');
    })

    $('#table-parcelles').DataTable({
        pageLength: 4,
        responsive: true,
        autoWidth: false,
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
        order: [],
        columnDefs: [
            {
                targets: 'no-sort',
                orderable: false,
            }
        ],
    });

    $('.kl-select-parent').on('change', function(){
        if ( $('#project_child_projectParent').val() ) {
            let parent_id = parseInt($('#project_child_projectParent').val(), 10);
            findLocationParent(parent_id);
        }
    });

    var parcel_id = 0;
    $('.delete-parcel').on('click', function (e) {
        parcel_id = $(this).data('parcel-id');
    });
    $('#confirmDeleteParcel').on('click', function (e) {
        deleteParcel(parcel_id);
    });
});

function findLocationParent(parentId) {
    $.ajax({
        url: url_parent_location,
        method: 'GET',
        data: {
            parent_id: parentId
        },
        success: function(response) {
            if (response.success == true && response.data) {
                $('#country').val(response.data.country);
                $('#region').val(response.data.region);
            }
        }
    });
}

function deleteParcel(id) {
    $.ajax({
        url: url_delete_parcel,
        method: 'DELETE',
        data: { id: id },
        success: function (response) {
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
            toastr.error('Erreur lors de la suppression');
        },
        complete: function () {
            $('#deleteParcelModal').modal('hide');
        }
    });
}
