// Validate in real-time the password and its confirmation
document.addEventListener('DOMContentLoaded', () => {
    const passwordInput = document.getElementById('password');
    const passwordConfirmInput = document.getElementById('password_confirm');
    const form = document.querySelector('form');
    
    const reqLength = document.getElementById('req-length');
    const reqMatch = document.getElementById('req-match');
    
    // Validation of the password length
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
    
    // Validation of password match
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
    
    // Events on the password input
    passwordInput.addEventListener('input', () => {
        validateLength();
        validateMatch();
    });
    
    // Events on the password confirmation input
    passwordConfirmInput.addEventListener('input', validateMatch);
    
    // Validation before submission
    form.addEventListener('submit', (e) => {
        const lengthValid = validateLength();
        const matchValid = validateMatch();
        
        if (!lengthValid || !matchValid) {
            e.preventDefault();
            alert('Veuillez corriger les erreurs dans le formulaire.');
        }
    });
});