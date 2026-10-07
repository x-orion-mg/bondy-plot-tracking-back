document.addEventListener("DOMContentLoaded", function () {
    function handleFileSelect(input, errorLabel, allowedExtensions) {
        if (input) {
            input.addEventListener("change", function () {
                const file = input.files[0];
                const label = input.nextElementSibling;
                const error = document.getElementById(errorLabel);
                const formGroup = input.closest(".form-group");

                // Recherche d'un éventuel élément d'info de fichier existant
                let fileInfoContainer = formGroup ? formGroup.querySelector(".file-info") : null;

                if (file) {
                    const fileName = file.name;
                    const fileSize = (file.size / 1024).toFixed(2) + " KB"; // Taille en Ko
                    const fileExtension = fileName.split('.').pop().toLowerCase();

                    // Vérification de l'extension
                    if (!allowedExtensions.includes(fileExtension)) {
                        if (error) error.style.display = "block";
                        input.value = ""; // Réinitialiser l'input si le fichier est invalide
                        if (label) label.textContent = "Choisissez un fichier";

                        // Supprimer l'élément d'info de fichier s'il existe
                        if (fileInfoContainer) {
                            fileInfoContainer.remove();
                        }
                        return;
                    } else {
                        if (error) error.style.display = "none";
                    }

                    // Mise à jour du label
                    if (label) label.textContent = fileName;

                    // Créer ou mettre à jour l'élément d'info de fichier
                    if (!fileInfoContainer && formGroup) {
                        fileInfoContainer = document.createElement("p");
                        fileInfoContainer.classList.add("file-info"); // Ajouter une classe pour l'identifier
                        formGroup.appendChild(fileInfoContainer);
                    }

                    // Affichage des informations du fichier
                    if (fileInfoContainer) {
                        fileInfoContainer.innerHTML = `<strong>Fichier sélectionné :</strong> ${fileName} (${fileSize})`;
                    }
                } else {
                    // Supprimer l'élément d'info de fichier si aucun fichier n'est sélectionné
                    if (fileInfoContainer) {
                        fileInfoContainer.remove();
                    }
                }
            });
        }
    }

    handleFileSelect(
        document.getElementById("customBoundaryFile"),
        "boundary-error",
        ["kml", "kmz", "gpx"]
    );

    handleFileSelect(
        document.getElementById("customFileTIFF"),
        "tiff-error",
        ["tiff", "tif", "geotiff", "gtiff"]
    );
});