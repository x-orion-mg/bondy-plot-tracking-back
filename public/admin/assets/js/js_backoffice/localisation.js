$(document).ready(function () {
    // Initialisation de DataTables
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

    // Initialisation de Select2
    $('.select2-example').select2({
        placeholder: 'Select'
    });

    function generateUrl(routeName, id) {
        return window.appConfig.urls[routeName].replace('ID_LOCALISATION', id);
    }

    // Gestion du modal de modification
    $('#editLocationModal').on('show.bs.modal', function (event) {
        const button = $(event.relatedTarget); // Bouton qui a déclenché le modal
        const modal = $(this);

        // Récupération des données
        const id = button.data('id');
        var ajaxUrl = button.data('editLocationModal');
        const country = button.data('country');
        const region = button.data('region');
        const shapeId = button.data('shape-id');
        const latitude = button.data('latitude');
        const longitude = button.data('longitude');
        const bounds = button.data('bounds');

        // Remplissage des champs du formulaire
        modal.find('select[name="country"]').val(country).trigger('change');
        modal.find('input[name="region"]').val(region);
        modal.find('input[name="shape_id"]').val(shapeId);
        modal.find('input[name="latitude"]').val(latitude);
        modal.find('input[name="longitude"]').val(longitude);
        modal.find('input[name="bounds"]').val(bounds);
        modal.find('input[name="id"]').val(id);
    });

    $('#viewLocationModal').on('show.bs.modal', function (event) {
        const button = $(event.relatedTarget); // Bouton qui a déclenché le modal
        const modal = $(this);

        // Récupération des données
        const country = button.data('country');
        const region = button.data('region');
        const shapeId = button.data('shape-id');
        const latitude = button.data('latitude');
        const longitude = button.data('longitude');
        const bounds = button.data('bounds');

        // Remplissage des champs du formulaire
        modal.find('select[name="country"]').val(country).trigger('change');
        modal.find('input[name="region"]').val(region);
        modal.find('input[name="shape_id"]').val(shapeId);
        modal.find('input[name="latitude"]').val(latitude);
        modal.find('input[name="longitude"]').val(longitude);
        modal.find('input[name="bounds"]').val(bounds);
    });
});
