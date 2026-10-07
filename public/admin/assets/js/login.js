document.addEventListener('DOMContentLoaded', function() {
    // Vérification du champ email
    document.getElementById('inputEmail').addEventListener('input', function() {
        //validateEmail(this);
    });

    // Vérification du champ mot de passe
    document.getElementById('inputPassword').addEventListener('input', function() {
        //validatePassword(this);
    });
});

function validateEmail(input) {
    let email = input.value.trim();
    let emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    let errorSpan = input.nextElementSibling;

    if (email === '') {
        setError(errorSpan, 'Veuillez entrer votre adresse e-mail.');
    } else if (!emailRegex.test(email)) {
        setError(errorSpan, 'Adresse e-mail invalide. Exemple : nom@domaine.com');
    } else {
        setSuccess(errorSpan);
    }
}

function validatePassword(input) {
    let password = input.value.trim();
    let errorSpan = input.nextElementSibling;

    if (password === '') {
        setError(errorSpan, 'Veuillez entrer votre mot de passe.');
    } else if (password.length < 8) {
        setError(errorSpan, 'Mot de passe trop court. Au moins 8 caractères sont nécessaires.');
    } else {
        setSuccess(errorSpan);
    }
}

function setError(span, message) {
    span.textContent = message;
    span.style.color = 'red';
}

function setSuccess(span) {
    span.textContent = '';
}