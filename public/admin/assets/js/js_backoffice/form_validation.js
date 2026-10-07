// CHECK FILE EXTENSION
$.validator.addMethod("fileType", function (value, element, param) {
    if (element.files.length === 0) {
        return false; // Aucun fichier sélectionné
    }
    const fileExtension = value.split(".").pop().toLowerCase();
    return param.includes(fileExtension); // Vérifie si l'extension est dans la liste
}, "Type de fichier non valide.");

$.validator.messages.required = "Ce champ est requis";

// CHECK SIZE FILE
$.validator.addMethod("fileSize", function (value, element, param) {
    let octets = element.files[0].size;
    let fileExtension = element.files[0].type.split('/')[0];
    let fileSize = (octets / 1048576).toFixed(2);
    let maxSize = param[fileExtension] || param.default;
    return fileSize <= maxSize;

}, function (param, element) {
    let fileExtension = element.files[0].type.split('/')[0];
    let maxSize = param[fileExtension] || param.default;
    return `Le fichier doit être inférieur ou égal à ${maxSize} Mo.`;
});

$.validator.setDefaults({
    ignore: ":hidden:not(.select2-example)" // Permet de valider Select2
});

function valid_form(formId, fileType = [], fileSize = {}, errorMsg = "Seules les images PNG ou JPG sont autorisées.") {
    // Ajout méthode regex personnalisée
    $.validator.addMethod("strongPassword", function (value, element) {
        return this.optional(element) || /^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[!@#$%^&*()_\-+=\[\]{};':"\\|,.<>\/?])[A-Za-z\d!@#$%^&*()_\-+=\[\]{};':"\\|,.<>\/?]{6,}$/.test(value);
    }, "Le mot de passe doit contenir au moins 6 caractères, une majuscule, une minuscule, un chiffre et un caractère spécial.");

    $(formId).validate({
        rules: {
            field: {
                required: true,
                email: true,
            },
            uploadFile: {
                required: true,
                fileType: fileType,
                fileSize: fileSize
            },
            email: {
                required: true,
                email: true
            },
            iconFile: {
                required: true,
                fileType: fileType,
                fileSize: fileSize
            },
            usage_type: {
                required: true,
            },
            csvFile: {
                required: true,
                fileType: fileType,
                fileSize: fileSize
            },
            user_password: {
                required: true,
                strongPassword: true
            },
            user_confirm_password: {
                required: true,
                equalTo: "#user-password"
            }
        },
        messages: {
            uploadFile: {
                required: "Veuillez sélectionner une image.",
                fileType: errorMsg,
            },
            email: {
                required: "Veuillez entrer une adresse e-mail.",
                email: "Veuillez entrer une adresse e-mail valide.",
            },
            iconFile: {
                required: "Veuillez sélectionner une image.",
                fileType: errorMsg,
            },
            usage_type: {
                required: "Veuillez choisir un type d'utilisation.",
            },
            csvFile: {
                required: "Veuillez sélectionner un fichier CSV.",
                fileType: "Seul le fichier CSV est autorisé.",
            },
            user_password: {
                required: "Veuillez entrer un mot de passe.",
            },
            user_confirm_password: {
                required: "Veuillez confirmer le mot de passe.",
                equalTo: "Les mots de passe ne correspondent pas."
            }
        }
    });
}


$('input:file').on('change', function (el) {
    $(this).val().length ? $(this).parent().find('.custom-file-label').text(el.currentTarget.files[0].name) : $(this).parent().find('.custom-file-label').text("");
})

//Banner Mobile
if (document.querySelector('.boxBanner')) {
    $('.boxBanner .ico-close').on('click', function () {
        let parent = $(this);
        parent.closest('.boxBanner').hide();
        parent.closest('.group-upload').find('.group-file').show()
    });
}


valid_form("#information_general", ["png", "jpg", "jpeg"], {image: 5});
valid_form("#gallery-edit", ["png", "jpg", "jpeg", "mp4", "webp"], {
    image: 20,
    video: 20
}, "Seules fichiers PNG, JPG, mp4, webm, ogg sont autorisées.");
valid_form("#sub-project-form", ["png", "jpg", "jpeg"], {image: 5}, "Seules fichiers PNG, JPG sont autorisées.");
valid_form("#subproject-new", ["png", "jpg", "jpeg", "svg"], {image: 5}, "Seules fichiers svg, png sont autorisées.");

valid_form("#sub-project-general-info", ["svg", "png", "jpg", "jpeg"], {image: 5}, "Seules fichiers svg, png, jpg, jpeg sont autorisées.");
valid_form("#sub-project-history");
valid_form("#sub-project-localisation");

valid_form("#login-form");
valid_form("#id-create-project-child", ["png", "jpg", "jpeg", "svg"], {image: 5}, "Seules fichiers svg, png sont autorisées.");
valid_form("#id-edit-project-child", ["png", "jpg", "jpeg", "svg"], {image: 5}, "Seules fichiers svg, png sont autorisées.");
valid_form("#id-form-partner", ["png", "jpg", "jpeg", "svg"], {image: 5}, "Seules fichiers svg, png sont autorisées.");
valid_form("#id-edit-project-type");
valid_form("#id-edit-location");
valid_form("#id-create-parcel", ["png", "jpg", "jpeg", "svg", 'tiff', 'gpx', 'kml'], {image: 20}, "Seules fichiers tiff, gpx, kml sont autorisées.");
valid_form("#id-edit-parcel", ["png", "jpg", "jpeg", "svg", 'tiff', 'gpx', 'kml'], {image: 20}, "Seules fichiers tiff, gpx, kml sont autorisées.");
valid_form('#id-form-story-telling', ["png", "jpg", "jpeg", "svg"], {image: 5}, "Seules fichiers svg, png sont autorisées.");


valid_form("#story-telling-new", ["png", "jpg", "jpeg", "svg"], {image: 5}, "Seules fichiers svg, png sont autorisées.");
valid_form("#id-form-tree-species", ["png", "jpg", "jpeg", "svg"], {image: 5}, "Seules fichiers svg, png sont autorisées.");
valid_form("#id-generate-trees");
valid_form("#id-form-usage-type", ["png", "jpg", "jpeg", "svg"], {image: 5}, "Seules fichiers svg, png sont autorisées.");
valid_form('#form-edit-users');

valid_form('#id-update-password');
valid_form('#id-resend-link');
valid_form('#id-form-create-tree-species', ["png", "jpg", "jpeg", "svg"], {image: 5}, "Seules fichiers svg, png sont autorisées.");

//import csv
valid_form("#customer-import", ["csv"], {text: 50}, "Seule fichier CSV est autorisée.");

// Handle form submission for new and edit
function handleFormSubmission(formSelector, successCallback, errorCallback) {
    $(formSelector).on('submit', function (event) {
        event.preventDefault();
        const form = $(this);
        const submitButton = form.find('button[type="submit"]');
        const spinner = '<span class="spinner"></span>';

        // Check if the form is valid
        if (form.valid()) {
            let formData = new FormData(this);
            $.ajax({
                url: form.attr('action'),
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
                        successCallback(response);
                    } else {
                        errorCallback(response);
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
    });
}

function toasts_error(message = 'Une erreur est survenue lors de l\'enregistrement.') {
    toastr.error(message);
}

// Handle form submission for edit and new modals
handleFormSubmission('#editGalerieModal form', function () {
    let text_btn = $('input[name="gallery_id"]').val() > 0 ? 'Modification' : 'Création';
    $('#editGalerieModal').modal('hide');
    toastr.success(text_btn + ' effectuée avec succès');
    setTimeout(() => {
        location.reload(); // Reload the page to reflect changes
    }, 2200);

}, function (response) {
    toasts_error();
});

handleFormSubmission('#editSubprojectModal form', function () {
    let text_btn = $('input[name="subproject_id"]').val() > 0 ? 'Modification' : 'Création';
    $('#editSubprojectModal').modal('hide');
    toastr.success(text_btn + ' effectuée avec succès');
    setTimeout(() => {
        location.reload();
    }, 2200);

}, function (response) {
    toasts_error();
});

handleFormSubmission('#editModalPartner form', function (response) {
    $('#editModalPartner').modal('hide');
    toastr.success(response.message);
    setTimeout(() => {
        location.reload(); // Reload the page to reflect changes
    }, 2200);
}, function (response) {
    if (response && response.success === false) {
        toastr.error(response.message);
    } else {
        toasts_error();
    }
});

// Handle form submission for subproject-new
handleFormSubmission('#subproject-new', function (response) {
    toastr.success('Création effectuée avec succès');
    setTimeout(() => {
        window.location.href = response.editUrl; // Redirect to the edit page
    }, 2200);
}, function (response) {
    toasts_error();
});

handleFormSubmission('#sub-project-general-info, #sub-project-history, #sub-project-localisation', function () {
    toastr.success('Modification effectuée avec succès');
    setTimeout(() => {
        location.reload();
    }, 2200);// Reload the page to reflect changes
}, function (response) {
    toasts_error();
});

handleFormSubmission('#editTypeModal form', function () {
    let text_btn = $('input[name="id"]').val() > 0 ? 'Modification' : 'Création';
    $('#editTypeModal').modal('hide');
    toastr.success(text_btn + ' effectuée avec succès');
    setTimeout(() => {
        location.reload();
    }, 2200);
}, function (response) {
    toasts_error();
});

handleFormSubmission('#story-telling-new', function (response) {
    toastr.success('Création effectuée avec succès');
    setTimeout(() => {
        window.location.href = response.editUrl; // Redirect to the edit page
    }, 2200);
}, function (response) {
    toasts_error();
});

handleFormSubmission('#story-telling-edit', function (response) {
    toastr.success('Modification effectuée avec succès');
    setTimeout(() => {
        location.reload(); // Redirect to the edit page
    }, 2200);
}, function (response) {
    toasts_error();
});

handleFormSubmission('#editLocationModal form', function () {
    $('#editLocationModal').modal('hide');
    toastr.success('Modification effectuée avec succès');
    setTimeout(() => {
        location.reload();
    }, 2200);
}, function (response) {
    toasts_error();
});

handleFormSubmission('#editTreeSpecyModal form', function () {
    $('#editTreeSpecyModal').modal('hide');
    toastr.success('Modification effectuée avec succès');
    setTimeout(() => {
        location.reload();
    }, 2200);
}, function (response) {
    toasts_error();
});

handleFormSubmission('#editUsageTypeModal form', function () {
    $('#editUsageTypeModal').modal('hide');
    let text_btn = $('input[name="id"]').val() > 0 ? 'Modification' : 'Création';
    toastr.success(text_btn + ' effectuée avec succès');
    setTimeout(() => {
        location.reload();
    }, 2200);
}, function (response) {
    toasts_error();
});

handleFormSubmission('#id-edit-parcel form', function (response) {
    toastr.success('Création effectuée avec succès');
    setTimeout(() => {
        location.reload();// Redirect to the edit page
    }, 2200);
}, function (response) {
    toasts_error();
});

handleFormSubmission(
    '#customer-import',
    function (response) {
        $('#reponse-import .modal-content').html(response.message);
        $('#reponse-import').modal('show');
    },
    function (response) {
        $('#reponse-import .modal-content').html(response.message);
        $('#reponse-import').modal('show');
    });

handleFormSubmission(
    '#generate-certificate-pdf',
    function (response) {
        toastr.success(response.message);
        setTimeout(() => {
            location.reload();
        }, 2200);
    },
    function (response) {
        toasts_error(response.message);
    });

handleFormSubmission(
    '#generate-report-pdf',
    function (response) {
        toastr.success(response.message);
        setTimeout(() => {
            location.reload();
        }, 2200);
    },
    function (response) {
        toasts_error(response.message);
    });

handleFormSubmission('#editUserModal form', function (response) {
    $('#editUserModal').modal('hide');
    toastr.success(response.message);
    setTimeout(() => {
        location.reload(); // Reload the page to reflect changes
    }, 2200);
}, function (response) {
    if (response && response.success === false) {
        toastr.error(response.message);
    } else {
        toasts_error();
    }
});

// Handle deletion
$('#confirmDelete').on('click', function () {
    const deleteUrl = $(this).data('delete-url');
    $.ajax({
        url: deleteUrl,
        type: 'DELETE',
        success: function (response) {
            if (response.success) {
                $('#deleteModal').modal('hide');
                toastr.success('L\'élément a été supprimé avec succès.');
                setTimeout(() => {
                    location.reload();
                }, 2200);
            } else if (typeof response.message !== 'undefined') {
                toastr.error(response.message);
            } else {
                toastr.error('Une erreur est survenue lors de la suppression.');
            }
        },
        error: function () {
            toastr.error('Une erreur est survenue lors de la suppression.');
        }
    });
});

function downloadCSV(url, filename) {
    fetch(url)
        .then(response => response.blob())
        .then(blob => {
            const link = document.createElement('a');
            link.href = window.URL.createObjectURL(blob);
            link.download = filename;
            document.body.appendChild(link);
            link.click();
            document.body.removeChild(link);
        })
        .catch(error => console.error('Errer lors du téléchargement:', error));
}

function initializeUserOptions() {
    document.querySelectorAll(".user-option").forEach(element => {
        let optionName = element.getAttribute("data-option");
        let updateUrl = element.getAttribute("data-option-url");

        if (!updateUrl) {
            console.error("Problème de configuration: URL manquante pour l'option", optionName);
            return;
        }

        // 🎯 Gestion des checkboxes
        if (element.type === "checkbox") {
            element.addEventListener("change", function () {
                let optionValue = this.checked;
                let label = this.nextElementSibling;

                this.disabled = true;
                label.style.opacity = "0.5";

                updateUserOption(updateUrl, optionName, optionValue, () => {
                    this.disabled = false;
                    label.style.opacity = "1";
                }, () => {
                    this.checked = !optionValue; // Annuler le changement en cas d'erreur
                });
            });
        }
    });

    // 🎯 Gestion des champs texte (avec bouton "Enregistrer")
    document.querySelectorAll(".save-option").forEach(button => {
        button.addEventListener("click", function () {
            let optionName = this.getAttribute("data-option");
            let cardBody = this.closest(".card-body");
            let inputField = cardBody ? cardBody.querySelector(`.user-option[data-option="${optionName}"]`) : null;

            if (inputField) {
                let optionValue = inputField.value.trim(); // Supprime les espaces inutiles
                let updateUrl = inputField.getAttribute("data-option-url");

                // 🎯 Validation : vérifier que la valeur est un nombre et <= 4 chiffres
                if (!/^\d{4}$/.test(optionValue)) {
                    toastr.error("Le code doit contenir exactement 4 chiffres !");
                    inputField.classList.add("is-invalid"); // Ajoute une bordure rouge pour signaler l'erreur
                    return;
                } else {
                    inputField.classList.remove("is-invalid"); // Supprime l'erreur visuelle si la valeur est correcte
                }

                button.disabled = true; // Désactiver le bouton pendant l'opération

                updateUserOption(updateUrl, optionName, optionValue, () => {
                    button.disabled = false;
                });
            } else {
                console.error("Champ input non trouvé pour l'option :", optionName);
            }
        });
    });
}

// 🎯 Fonction générique pour envoyer une requête AJAX
function updateUserOption(url, optionName, optionValue, onSuccess = null, onError = null) {
    fetch(url, {
        method: "POST",
        headers: {
            "Content-Type": "application/json",
            "X-Requested-With": "XMLHttpRequest"
        },
        body: JSON.stringify({
            optionName: optionName,
            optionValue: optionValue
        })
    })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                toastr.success("Option mise à jour !");
                if (onSuccess) onSuccess();
            } else {
                toastr.error("Erreur lors de la mise à jour !");
                if (onError) onError();
            }
        })
        .catch(error => {
            console.error("Erreur AJAX :", error);
            toastr.error("Une erreur est survenue !");
            if (onError) onError();
        });
}

document.addEventListener("DOMContentLoaded", function () {
    initializeUserOptions();
});
