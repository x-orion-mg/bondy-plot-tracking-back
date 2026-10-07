$(document).ready(function () {
    //editor
    if ($('.summernote-editor').length > 0) {
        $('.summernote-editor').summernote({
            placeholder: 'Contenu...',
            tabsize: 2,
            height: 100,
        });
    }
});
