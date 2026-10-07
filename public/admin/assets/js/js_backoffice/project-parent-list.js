$(document).ready(function (){
    $('.select2-example').select2({
        placeholder: 'Select'
    });

    var table = $('#table-projects-bondy').DataTable({
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
            url: url_ajax_prj_parent,
            data: function(d) {
                // Append the selected filters to the DataTable request
                d['status_id'] = $('#project-status').val();
                d['type_id']   = $('#project-type').val();
            }
        },
        columns: [
            {
                data: 'title',
                className: '',
                render: function (data, type, row) {
                    return `<span >${row.title} </span>`;
                }
            },
            {
                data: null,
                className: '',
                orderable: false,
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
                render : function(data, type, row) {
                    return `<td class="text-center">${row.treeCount}</td>`;
                }
            },
            {
                data: null,
                className: 'text-center',
                render: function (data, type, row) {
                    return `<span class="badge bg-info-bright text-info">${row.status}</span>`;
                }
            },
            {
                data: null,
                className: 'text-center',
                render: function (data, type, row) {
                    return `<span class="badge text-white" style="background-color: ${row.color}">${row.type} </span>`;
                }
            },
            {
                data: 'id',
                className: 'text-center',
                render: function(data, type, row) {
                    var href_edit = data ? url_edit_prj_parent : "javascript:void(0)";
                    var href_show = data ? url_show_prj_parent : "javascript:void(0)";
                    href_edit = href_edit.replace('0', data);
                    href_show = href_show.replace('0', data);
                    return `<a href="${href_show}" class="text-info" data-toggle="tooltip" title="Visualiser"><i class="ti-eye"></i></a>
                        <a href="${href_edit}" class="text-secondary ml-2" data-toggle="tooltip" title="Modifier"><i class="ti-pencil"></i></a>`;
                }
            }
        ],
        "order": [],
        "columnDefs": [ {
            "targets": 'no-sort',
            "orderable": false,
        } ],
    });

    $('#project-status, #project-type').on('change', function() {
        table.ajax.reload();
    });
});
