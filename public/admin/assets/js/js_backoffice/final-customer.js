$(document).ready(function () {
   // Vérifier si la DataTable est déjà initialisée
   let table = $('#table-final-customers').DataTable({
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
         url: url_list_final_customers,
         data: function (d) {
            d['dateStart'] = $('#dateStart').val();
            d['dateEnd'] = $('#dateEnd').val();
         }
      },
      columns: [
         {
            data: 'Nom',
            className: '',
            render: function (data, type, row) {
               let lastName = row.lastName ? row.lastName : '';
               let firstName = row.firstName ? row.firstName : '';
               return `<span>${lastName} ${firstName}</span>`;
            }
         },
         {
            data: 'email',
            className: '',
            render: function (data, type, row) {
               return `<span>${row.email}</span>`;
            }
         },
         {
            data: 'createdAt',
            className: '',
            render: function (data, type, row) {
               return `<span>${row.createdAt}</span>`;
            }
         },
         {
            data: 'userId',
            className: 'text-center',
            render: function (data) {
               let href_download = data ? url_export_qrcode_final_customer : "javascript:void(0)";
               href_download = href_download.replace('0', data);
               return `<a href="javascript:void(0)" class="text-secondary" data-toggle="tooltip" title="Download" onclick="downloadFile('${href_download}')">
                    <i class="ti-download"></i>
                </a>`;
            }
         }
      ],
      "order": [],
      "columnDefs": [
         {
            "targets": 'no-sort',
            "orderable": false,
         }
      ]
   });

   let datefilter = $('input[name="daterangepicker"]');
   datefilter.daterangepicker({
      autoUpdateInput: false,
      locale: {
         format: 'DD/MM/YYYY', // Format français
         applyLabel: "Appliquer",
         cancelLabel: "Annuler",
         daysOfWeek: ["Dim", "Lun", "Mar", "Mer", "Jeu", "Ven", "Sam"],
      }
   });

   datefilter.on('apply.daterangepicker', function(ev, picker) {
      $(this).val(picker.startDate.format('DD/MM/YYYY') + ' - ' + picker.endDate.format('DD/MM/YYYY'));
      $('#dateStart').val(picker.startDate.format('DD/MM/YYYY'));
      $('#dateEnd').val(picker.endDate.format('DD/MM/YYYY'));
      table.ajax.reload();
   });

   $('input.create-event-datepicker').daterangepicker({
      singleDatePicker: true,
      showDropdowns: true,
      autoUpdateInput: false
   }).on('apply.daterangepicker', function(ev, picker) {
      $(this).val(picker.startDate.format('DD/MM/YYYY'));
   });

   datefilter.on('cancel.daterangepicker', function(ev, picker) {
      $(this).val('');
      $('#dateStart').val('');
      $('#dateEnd').val('');
      table.ajax.reload();
   });
});

function downloadFile(url) {
   fetch(url)
       .then(response => response.json())
       .then(data => {
          if (data.status === true) {
             let dataUrls = data.fileUrls;
             if (dataUrls.length > 0) {
                dataUrls.forEach(fileUrl => {
                   downloadCSV(fileUrl.file_path, fileUrl.file_name);
                });
             }
             toastr.success('L\'élément a été téléchargé avec succès.');
          } else {
             toastr.error('Error downloading the file:', data);
          }
       })
       .catch(error => {
          toastr.error('Erreur lors du téléchargement du fichier!');
       });
}