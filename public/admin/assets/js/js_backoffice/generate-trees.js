$(document).ready(function () {
    // Gestion du formulaire d'envoi
    $('#submitTreeForm').on('click', function (e) {
        let treesData = [];
        let formId = $(this).closest('form').attr('id');
        document.querySelectorAll(".tree-species").forEach((select, index) => {
            let speciesId = select.value;
            let selectedOption = select.options[select.selectedIndex]; // Récupérer l'option sélectionnée
            let speciesName = selectedOption.dataset.speciesName; // Lire data-species-name
            let treeNumber = document.querySelectorAll(".tree-number")[index].value;

            if (speciesId && speciesName && treeNumber && speciesName.trim() !== "" && treeNumber > 0) {
                treesData.push({
                    id: speciesId,
                    name: speciesName,
                    number: Number(treeNumber)
                });
            }            
        });

        if (treesData.length === 0) {
            document.getElementById("trees").scrollIntoView({ behavior: "smooth" });
            $('#generate-trees-error').removeClass('d-none');
            return;
        } else {
            $('#generate-trees-error').addClass('d-none');
            let treesDataJSON = JSON.stringify(treesData);
            $('#treesDataInput').val(treesDataJSON);
            postTrees(formId);
        }
    });
});

function postTrees(formSelector) {
    const form = $(`#${formSelector}`);

    // Check if the form is valid
    if (form.valid()) {
        const submitButton = form.find('button[type="submit"]');
        const spinner = '<span class="spinner"></span>';
        
        const formElement = form[0];
        const formData = new FormData(formElement);

        $.ajax({
            url: url_generate_tree,
            type: form.attr('method'),
            data: formData,
            processData: false,
            contentType: false,
            beforeSend: function () {
                // Add the animation class to the form
                if (!submitButton.hasClass('loading')) {
                    submitButton.addClass('loading').html(spinner);
                    submitButton.attr('disabled', true);
                }
            },
            success: function (response) {
                if (response.success) {
                    toastr.success('Génération des arbres effectuée avec succès');
                    setTimeout(() => {
                        location.reload();
                    }, 2200);
                } else {
                    toastr.error('Une erreur est survenue lors de l\'enregistrement.');
                }
            },
            error: function () {
                toastr.error('Une erreur est survenue lors de l\'enregistrement.');
            },
            complete: function () {
                setTimeout(() => {
                    submitButton.removeClass('loading').html("Enregistrer");
                    submitButton.attr('disabled', false);
                }, 2200);
            }
        });
    }
}
