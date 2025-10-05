// Validation en temps réel du formulaire de réinitialisation
document.addEventListener('DOMContentLoaded', () => {
    const passwordInput = document.getElementById('password');
    const passwordConfirmInput = document.getElementById('password_confirm');
    const form = document.querySelector('form');
    
    const reqLength = document.getElementById('req-length');
    const reqMatch = document.getElementById('req-match');
    
    // Validation de la longueur du mot de passe
    function validateLength() {
        const isValid = passwordInput.value.length >= 8;
        
        if (passwordInput.value.length === 0) {
            reqLength.className = '';
            reqLength.textContent = '✓ Au moins 8 caractères';
        } else if (isValid) {
            reqLength.className = 'valid';
            reqLength.textContent = '✓ Au moins 8 caractères';
        } else {
            reqLength.className = 'invalid';
            reqLength.textContent = '✗ Au moins 8 caractères';
        }
        
        return isValid;
    }
    
    // Validation de la correspondance des mots de passe
    function validateMatch() {
        const password = passwordInput.value;
        const passwordConfirm = passwordConfirmInput.value;
        
        if (passwordConfirm.length === 0) {
            reqMatch.className = '';
            reqMatch.textContent = '✓ Les deux mots de passe doivent correspondre';
            passwordConfirmInput.setCustomValidity('');
            return true;
        }
        
        const isValid = password === passwordConfirm;
        
        if (isValid) {
            reqMatch.className = 'valid';
            reqMatch.textContent = '✓ Les mots de passe correspondent';
            passwordConfirmInput.setCustomValidity('');
        } else {
            reqMatch.className = 'invalid';
            reqMatch.textContent = '✗ Les mots de passe ne correspondent pas';
            passwordConfirmInput.setCustomValidity('Les mots de passe ne correspondent pas');
        }
        
        return isValid;
    }
    
    // Événements sur le mot de passe
    passwordInput.addEventListener('input', () => {
        validateLength();
        validateMatch();
    });
    
    // Événements sur la confirmation
    passwordConfirmInput.addEventListener('input', validateMatch);
    
    // Validation avant soumission
    form.addEventListener('submit', (e) => {
        const lengthValid = validateLength();
        const matchValid = validateMatch();
        
        if (!lengthValid || !matchValid) {
            e.preventDefault();
            alert('Veuillez corriger les erreurs dans le formulaire.');
        }
    });
});