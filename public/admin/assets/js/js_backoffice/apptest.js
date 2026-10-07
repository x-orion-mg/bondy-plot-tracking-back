$(document).ready(function() {
    
    // Fonction pour afficher le formulaire dans le modal lors du clic sur le bouton "Ajouter"
    $('.btn-create').click(function(e) {
        e.preventDefault();
        
        // Nettoyage des champs existants
        $('#editModal .modal-body').empty();
        
        // Affichage du titre approprié
        $('#editModal .modal-title').text('Ajouter un nouveau type de projet');
        
        // Création du formulaire pour l'ajout
        var formHtml = `
            <form action="{{ path('project_type_create') }}" method="post">
                <div class="form-group">
                    <label>Type de projet</label>
                    <input type="text" class="form-control" name="label" required>
                </div>
                
                <div class="form-group">
                    <label>Couleur</label>
                    <div class="input-group colorpicker-example">
                        <input type="text" class="form-control" id="color" name="color" required>
                        <div class="input-group-append">
                            <span class="input-group-text"><i></i></span>
                        </div>
                    </div>
                </div>
                
                <button type="submit" class="btn btn-primary">Enregistrer</button>
            </form>
        `;
        
        $('#editModal .modal-body').html(formHtml);
    });
    
    // Fonction pour afficher le formulaire dans le modal lors du clic sur un lien "Modifier"
    $('.btn-edit').click(function(e) {
        e.preventDefault();
        var id = $(this).data('id');
        var label = $(this).data('label');
        var color = $(this).data('color');
        
        // Nettoyage des champs existants
        $('#editModal .modal-body').empty();
        
        // Affichage du titre approprié
        $('#editModal .modal-title').text('Modifier le type de projet');
        
        // Création du formulaire pour la modification
        var formHtml = `
            <form action="{{ path('project_type_update') }}" method="post">
                <input type="hidden" name="id" value="${id}">
                <div class="form-group">
                    <label>Type de projet</label>
                    <input type="text" class="form-control" name="label" value="${label}" required>
                </div>
                
                <div class="form-group">
                    <label>Couleur</label>
                    <div class="input-group colorpicker-example">
                        <input type="text" class="form-control" id="color" name="color" value="${color}" required>
                        <div class="input-group-append">
                            <span class="input-group-text"><i></i></span>
                        </div>
                    </div>
                </div>
                
                <button type="submit" class="btn btn-primary">Enregistrer</button>
            </form>
        `;
        
        $('#editModal .modal-body').html(formHtml);
    });
    
    // Gestion des soumissions du formulaire
    $(document).on('submit', '#editModal form', function(e) {
        e.preventDefault();
        var $form = $(this);
        $.ajax({
            url: url_create,
            method: $form.attr('method'),
            data: $form.serialize(),
            success: function(response) {
                if (response.success) {
                    // Mise à jour de la liste après succès
                    if (response.success == true) {
                        toastr.success('Ajout/modification effectuée avec successe');
                        $('#editModal').modal('hide');

                        // Après avoir reçu les nouvelles données
$('#myTable').DataTable().rows.add(newData).draw();

                        //location.reload();
                    } else {
                        toastr.success('Erreur lors de l\'ajout/modification');
                    }
                } else {
                    toastr.success('Erreur lors de l\'ajout/modification');
                }
            },
            error: function(xhr, status, error) {
                console.error("Une erreur s'est produite:", error);
            }
        });
    });

    /* $('#myTable').DataTable({
        ajax: {
          url: url_list,
          dataSrc: function (json) {
            console.log(json);
            // Traiter les données ici si nécessaire
            return json;
          }
        },
        columns: [
          { data: 'id' },
          { data: 'label' },
          { data: 'color' },
          { data: 'actions' }
        ]
    }); */
});
