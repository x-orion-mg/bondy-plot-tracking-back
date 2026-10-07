$(document).ready(function (){
    let project_id = 0;
    $('.select2-example').select2({
        placeholder: 'Select'
    });

    // Initialize the DataTable
    var table = $('#myTable').DataTable({
        "responsive": true,
        "autoWidth": false,
        "paging": true,
        language: {
            paginate: {
                next: '>',
                previous: '<'
            },
            search: 'Rechercher',
            lengthMenu: 'Afficher _MENU_ éléments',
            info: 'Affichage de _START_ à _END_ sur _TOTAL_ éléments',
            infoEmpty: 'Affichage de 0 à 0 sur 0 éléments',
            infoFiltered: '(filtré à partir de _MAX_ éléments au total)',
            emptyTable: 'Aucune donnée disponible',
            infoEmpty: 'Aucune donnée disponible',
            processing: 'Traitement en cours...',
            zeroRecords: 'Aucun élément correspondant trouvé',
            aria: {
                sortAscending: ': activer pour trier la colonne par ordre croissant',
                sortDescending: ': activer pour trier la colonne par ordre décroissant'
            }
        },
        processing: true,
        serverSide: true,
        ajax: {
            url: url_ajax_prj_partner,
            data: function(d) {
                d['partner_id'] = $('#partnerFilter').val();
                d['step_id'] = $('#projectStepFilter').val();
            }
        },
        columns: [
            {
                data: 'logo',
                className: 'text-center',
                render : function(data, type, row) {
                    return `<img src="${row.logo}" alt="Logo" class="img-fluid" style="width: 50px; height: 50px; border-radius: 50%;">`;
                }
            },
            { data: 'name', className: '' },
            {
                data: null,
                className: 'text-center',
                render: function (data, type, row) {
                    return `<i class="fa fa-map-marker"></i> ${row.location}`;
                }
            },
            {
                data: 'startDate',
                className: 'text-center',
                render : function(data, type, row) {
                    return `<span class="badge badge-pill bg-info">
                                <i class="fa fa-calendar"></i>
                                    <span class="font-weight-light">${row.startDate}</span>
                            </span>`;
                }
            },
            {
                data: null,
                className: 'text-center',
                render: function (data, type, row) {
                    return `<span class="badge text-white" style="background-color: ${row.color}">${row.stepName} </span>`;
                }
            },
            {
                data: 'id',
                className: 'text-center',
                render: function(data, type, row) {
                    var href_edit = data ? url_edit_prj_partner : "javascript:void(0)";
                    var href_delete = data ? url_delete_prj_partner : "javascript:void(0)";
                    var href_show = data ? url_show_prj_partner : "javascript:void(0)";
                    href_edit = href_edit.replace('0', data);
                    href_delete = href_delete.replace('0', data);
                    href_show = href_show.replace('0', data);
                    return `
                        <a href="${href_show}" class="text-info" data-toggle="tooltip" title="Visualiser"><i class="ti-eye"></i></a>
                        <a href="${href_edit}" class="text-secondary ml-2" data-toggle="tooltip" title="Modifier"><i class="ti-pencil"></i></a>
                        <a href="${href_delete}" class="text-danger ml-2 btn-delete" data-toggle="modal" data-delete-url="${href_delete}" data-target="#deleteModal" data-delete-id="${row.id}">
                            <i class="ti-trash" data-toggle="tooltip" title="Supprimer"></i>
                        </a>
                    `;
                }
            }
        ],
        "order": [],
        "columnDefs": [ {
            "targets": 'no-sort',
            "orderable": false,
        } ]
    });

    // Event listener for the partner filter or project step filter
    $('#partnerFilter, #projectStepFilter').on('change', function() {
        table.ajax.reload();
    });

    $('.btn-confirm-delete').on('click', function (e) {
        project_id = $('this').data('delete-id');
        let url = $('.btn-delete').attr('href');
    });
});
