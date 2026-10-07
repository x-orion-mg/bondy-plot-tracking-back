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
    $('.select2-example').select2({
        placeholder: 'Select'
    });

    $('#editTreeSpecyModal').on('show.bs.modal', function (event) {
        var button  = $(event.relatedTarget);
        var modal   = $(this);
        var id      = button.data('id') ?? 0;
        var icon    = button.data('icon') ?? '';
        var commonName = button.data('name') ?? '';
        var scientificName = button.data('scientific-name') ?? '';
        var usageType = button.data('usage-type') ?? ''; // ID de l'usage type
    
        modal.find('.modal-body [name="id"]').val(id);
        modal.find('.modal-body [name="common_name"]').val(commonName);
        modal.find('.modal-body [name="scientific_name"]').val(scientificName);
        if (icon) {
            modal.find('.boxBanner_img').attr('src', icon);
            modal.find('.group-file').hide();
            modal.find('.boxBanner').show();
        } else {
            modal.find('.boxBanner').hide();
            modal.find('.group-file').show();
        }
        
        // Sélectionne l'option correspondant à usageType dans le select
        modal.find('.modal-body select.select2-example').val(usageType).trigger('change');
    });
    $("#uploadFile").change(function() {
        let file = this.files[0];
        if (file) {
            let reader = new FileReader();
            reader.onload = function(e) {
                $("#icon2").attr("src", e.target.result);
            };
            reader.readAsDataURL(file);
        }
    });

    let magnificPopupGalleryConfig = {
        type: 'image',
        gallery: {
            enabled: true
        }
    };
    $('.image-popup-gallery-item').magnificPopup(magnificPopupGalleryConfig);
});