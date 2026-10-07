$(document).ready(function (){
    $('#myTable').DataTable({
        pageLength: 10,
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
    $('input.colorpicker-example').colorpicker({
        format: 'hex'
    });
    $('#editUsageTypeModal').on('show.bs.modal', function (event) {
        const button = $(event.relatedTarget); // Bouton qui a déclenché le modal
        const modal = $(this);

        // Récupération des données
        const id = button.data('id');
        const name = button.data('label');
        const icon = button.data('icon');

        // Remplissage des champs du formulaire
        modal.find('input[name="id"]').val(id);
        modal.find('input[name="label"]').val(name);
        if (icon) {
            modal.find('.boxBanner_img').attr('src', icon);
            modal.find('.group-file').hide();
            modal.find('.boxBanner').show();
        } else {
            modal.find('.boxBanner').hide();
            modal.find('.group-file').show();
        }
    });
});