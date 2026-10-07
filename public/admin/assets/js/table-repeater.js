function init_table_repeatable(_context) {
    if ($('.table-repeatable:not(.loaded)', _context).length > 0) {
        $('.table-repeatable:not(.loaded)', _context).each(function () {
            var _this = $(this);

            // Utiliser la ligne modèle cachée pour le clonage
            var _clone = _this.find('tr.clone').first().clone();

            _count_col = $(this).find('thead > tr > *').length;
            _this.append($('<tfoot><tr><td colspan="' + _count_col + '"><button type="button" class="table-rep-add-button btn btn-outline-light float-right" id="add-line-button"><i class="fa fa-plus mr-2"></i> Ajouter une ligne</button></td></tr></tfoot>'));

            // Fonction pour vérifier si toutes les options sont sélectionnées
            function checkAllSpeciesSelected() {
                let allSelected = true;

                document.querySelectorAll(".tree-species").forEach(select => {
                    if (!select.value) {
                        allSelected = false;
                    }
                });

                // Cacher ou afficher le bouton "Ajouter une ligne"
                if (allSelected === true) {
                    $('#add-line-button').hide();
                } else {
                    $('#add-line-button').show();
                }
            }

            // Mettre à jour les options des espèces pour les selects
            function updateSpeciesOptions() {
                let selectedSpecies = new Set();

                // Récupérer toutes les espèces déjà sélectionnées
                document.querySelectorAll(".tree-species").forEach(select => {
                    if (select.value) {
                        selectedSpecies.add(select.value);
                    }
                });

                // Mettre à jour chaque <select>
                document.querySelectorAll(".tree-species").forEach(select => {
                    let currentValue = select.value;

                    select.querySelectorAll("option").forEach(option => {
                        if (option.value && option.value !== currentValue) {
                            option.disabled = selectedSpecies.has(option.value);
                        } else {
                            option.disabled = false;
                        }
                    });
                });

                checkAllSpeciesSelected();
            }

            // Mettre à jour les options quand une sélection change
            _this.on('change', '.tree-species', function () {
                updateSpeciesOptions();
            });

            // Click pour ajouter une ligne
            _this.on('click', '.table-rep-add-button', function () {
                var _parent = $(this).parents('table');
                var _cloned = _clone.clone(); // Cloner la ligne modèle

                // Réinitialiser les valeurs des éléments clonés
                _cloned.find('select').val('').trigger('change');
                _cloned.find('input[type="number"]').val('');

                _cloned.removeClass('clone').addClass('cloned');
                _parent.find('tbody').append(_cloned);
                _cloned.show();

                // Réinitialiser Select2
                if (typeof $.fn.select2 == 'function') {
                    _cloned.find('span.select2-container').remove();
                    _cloned.find('select.select2-example').removeClass('select2-hidden-accessible');
                    _cloned.find('select.select2-example').select2();
                }

                // Ajouter le bouton de suppression
                _cloned.find('td:last-child').append('<a class="table-rep-trash-button" href="#" title="Supprimer la ligne"><i class="fa fa-trash"></i></a>');

                updateSpeciesOptions();
            });

            // Event de suppression
            $('body').on('click', '.table-rep-trash-button', function () {
                $(this).parents('tr').remove();
                updateSpeciesOptions();
                return false;
            });

            // Initialiser les options dès que la page est chargée pour les lignes existantes
            updateSpeciesOptions();

            // Marquer la table comme initialisée
            _this.addClass('loaded');
        });
    }
}