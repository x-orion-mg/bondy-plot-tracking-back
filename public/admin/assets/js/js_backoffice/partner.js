let text_btn = 'Modification';
let partner_id = 0;

$(document).ready(function () {
    $('.btn-create-partner').on('click', function (e) {
        text_btn = 'Création';
    });
    $('.btn-edit-partner').on('click', function (e) {
        let name = $(this).data('name');
        let email = $(this).data('email');
        let phone = $(this).data('phone');
        let address = $(this).data('address');
        let logo = $(this).data('logo');
        let id = $(this).data('id');
        let project_count = $(this).data('project-count');

        $('#editModal .modal-body #name').val(name);
        $('#editModal .modal-body #email').val(email);
        $('#editModal .modal-body #phone').val(phone);
        $('#editModal .modal-body #address').val(address);
        $('#editModal .modal-body #logo').val(logo);
        $('#editModal .modal-body #project_count').val(project_count);

        $('input[name="id"]').val(id ?? 0);
    });
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
    $('.select2-example').select2({
        placeholder: 'Select'
    });

    $('#editModalPartner').on('show.bs.modal', function (event) {
        $(this).find('form')[0].reset();
        $(this).find('.error').remove();
        $('label.custom-file-label').text('Choisissez un fichier');
        const button = $(event.relatedTarget); // Bouton qui a déclenché le modal

        partner_id = button.data('id') ?? 0;
        let partner_name = button.data('name') ?? '';
        let partner_email = button.data('email') ?? '';
        let partner_phone = button.data('phone') ?? '';
        let partner_address = button.data('address') ?? '';
        let partner_logo = button.data('logo') ?? '';
        let project_count = button.data('project-count');
        let modal = $(this);

        modal.find('.modal-body [name="partner_name"]').val(partner_name);
        modal.find('.modal-body [name="partner_email"]').val(partner_email);
        modal.find('.modal-body [name="partner_phone"]').val(partner_phone);
        modal.find('.modal-body [name="partner_address"]').val(partner_address);
        modal.find('.modal-body [name="project_count"]').val(project_count);

        if (partner_logo) {
            modal.find('.boxBanner_img').attr('src', partner_logo);
            modal.find('.group-file').hide();
            modal.find('.boxBanner').show();
        } else {
            modal.find('.boxBanner').hide();
            modal.find('.group-file').show();
        }
        modal.find('input[name="partner_id"]').val(partner_id ?? 0);
        modal.find('form').attr('action', url_create_partner);
    });

    $('.btn-delete-partner').on('click', function (e) {
        e.preventDefault();
        partner_id = $(this).data('id');
    });

    $('.btn-confirm-delete').on('click', function (e) {
        e.preventDefault();
        deletePartner(partner_id);
    });

    $('.btn-find-partner-url').on('click', function (e) {
        findPartnerUrl( $(this).data('user-id') );
    });
});

function deletePartner(id) {
    $.ajax({
        url: url_delete_partner,
        method: 'POST', // Use POST
        dataType: 'json',
        data: {
            id: id,
            _method: 'DELETE', // Symfony interprets this as DELETE
            _token: csrf_token // Include CSRF token
        },
        success: function (response) {
            if (response.status === true) {
                $('#deleteModal').modal('hide');
                toastr.success(response.message || 'Partenaire supprimé avec succès');
                setTimeout(() => {
                    location.reload();
                }, 2200);
            } else {
                $('#deleteModal').modal('hide');
                toastr.error(response.message || 'Erreur lors de la suppression');
            }
        },
        error: function (xhr) {
            $('#deleteModal').modal('hide');
            toastr.error('Erreur lors de la suppression');
        }
    });
}

function findPartnerUrl(id) {
    $.ajax({
        url: url_find_partner_link,
        method: 'GET',
        dataType: 'json',
        data: { id: id },
        success: function (response) {
            if (response.status === true && response.data) {
                navigator.clipboard.writeText(response.data).then(() => {
                    toastr.success('URL copiée dans le presse-papiers');
                }).catch(err => {
                    toastr.error('Impossible de copier l\'URL');
                });
            } else {
                toastr.error('Erreur lors de la recherche de l\'URL');
            }
        },
        error: function (xhr) {
            toastr.error('Erreur lors de la recherche de l\'URL');
        }
    });
}