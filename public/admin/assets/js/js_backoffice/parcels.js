$(document).ready(function (){
    init_table_repeatable($('#editTreeSpecies'));

    $('.select2-example').select2({
        placeholder: 'Selectionner'
    });

    $('input.datepick').daterangepicker({
        singleDatePicker: true,
        showDropdowns: true,
        locale: {
            format: 'DD/MM/YYYY', // Format français
            applyLabel: "Appliquer",
            cancelLabel: "Annuler",
            daysOfWeek: ["Dim", "Lun", "Mar", "Mer", "Jeu", "Ven", "Sam"],
            monthNames: [
                "Janvier", "Février", "Mars", "Avril", "Mai", "Juin",
                "Juillet", "Août", "Septembre", "Octobre", "Novembre", "Décembre"
            ],
            firstDay: 1 // Lundi comme premier jour de la semaine
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
    });

    $('#id-edit-parcel').on('submit', function(e) {
        e.preventDefault();
        let formData = new FormData(this);
        $.ajax({
            url: $(this).attr('action'),
            type: $(this).attr('method'),
            data: formData,
            processData: false,
            contentType: false,
            success: function(response) {
                if(response.success === true) {
                    toastr.success(response.message);
                    setTimeout(() => {
                        location.reload();
                    }, 2200);
                } else {
                    toastr.error(response.message);
                }
            },
        });
    });

    $('#id-create-parcel').on('submit', function(e){
        e.preventDefault();
        //$('body .preloader').attr('style', 'display: flex;');

        // Select required fields
        let requiredFields = [
            'input[name="title"]',
            'input[name="city"]',
            'input[name="latitude"]',
            'input[name="longitude"]',
            'input[name="startDate"]',
            'select[name="plantingYear"]'
        ];
        let isValid = true;

        // Check if any required fields are empty
        requiredFields.forEach(function(field) {
            let fieldElement = $(field);
            if (fieldElement.length === 0 || fieldElement.val().trim() === '') {
                isValid = false;
                fieldElement.addClass('is-invalid');
            } else {
                fieldElement.removeClass('is-invalid');
            }
        });

        if (!isValid) {
            toastr.error('Veuillez remplir tous les champs obligatoires.');
            return;
        }

        let formData = new FormData(this);
        $.ajax({
            url: $(this).attr('action'),
            type: $(this).attr('method'),
            data: formData,
            processData: false,
            contentType: false,
            success: function(response) {
                if(response.success === true) {
                    toastr.success(response.message);
                    $('.preloader').attr('style', 'display: block;');
                    setTimeout(() => {
                        window.location.href = '/admin/parcel/' + response.parcelId + '/edit';
                    }, 2200);
                } else {
                    toastr.error(response.message);
                }
            },
        });
    });

});