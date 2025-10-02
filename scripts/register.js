const yearSelect = document.getElementById('year');
const tdSelect = document.getElementById('td');
const parcoursSelect = document.getElementById('parcours');

// On récupère la valeur du champ input email
const emailInput = document.getElementById('email');

// Désactivation TD4 si année 2 ou 3
const td4Option = Array.from(tdSelect.options).find(opt => opt.value === 'TD4');

yearSelect.addEventListener('change', () => {
    if (yearSelect.value === '2' || yearSelect.value === '3') {
        td4Option.disabled = true;
        parcoursSelect.disabled = false;
        if (tdSelect.value === 'TD4') {
            tdSelect.value = '';
        }
    } else {
        td4Option.disabled = false;
        parcoursSelect.disabled = true;
        parcoursSelect.value = '';
    }
});

// Désactivation des champs pour les enseignants et partenaires
const userTypeSelect = document.getElementById('user_type');
const tpSelect = document.getElementById('tp');
const etudiantFields = document.getElementById('etudiantFields');

function toggleStudentFields() {
    const isStudent = userTypeSelect.value === 'student';

    etudiantFields.style.display = isStudent ? 'block' : 'none';
    parcoursSelect.disabled = !isStudent;
    yearSelect.disabled = !isStudent;
    tdSelect.disabled = !isStudent;
    tpSelect.disabled = !isStudent;

    if (!isStudent) {
        parcoursSelect.value = '';
        yearSelect.value = '';
        tdSelect.value = '';
        tpSelect.value = '';
    }
}
const pwd = document.getElementById('pwd');
const pwdverif = document.getElementById('pwdverif');

function validatePassword() {
    if (pwd.value !== pwdverif.value) {
        pwdverif.setCustomValidity('Les mots de passe ne correspondent pas');
    } else {
        pwdverif.setCustomValidity('');
    }
}

pwd.addEventListener('change', validatePassword);
pwdverif.addEventListener('input', validatePassword);

// Appliquer immédiatement au chargement de la page (cas de rechargement après POST)
toggleStudentFields();

// Appliquer à chaque changement de statut
userTypeSelect.addEventListener('change', toggleStudentFields);