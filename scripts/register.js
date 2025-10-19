const yearSelect = document.getElementById('year');
const tdSelect = document.getElementById('td');
const parcoursSelect = document.getElementById('parcours');

// Get the email input field
const emailInput = document.getElementById('email');

// Disable TD4 if year is 2 or 3
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

// Disable fields for teachers and partners
const userTypeSelect = document.getElementById('userType');
const tpSelect = document.getElementById('tp');
const etudiantFields = document.getElementById('etudiantFields');
const idField = document.getElementById('id');

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


function toggleClientFields() {
    const isClient = userTypeSelect.value === 'client';

    idField.style.display = isClient ? 'block' : 'none';
    idField.disabled = !isClient;

    if (!isClient) {
        parcoursSelect.value = '';
        yearSelect.value = '';
        tdSelect.value = '';
        tpSelect.value = '';
    }

}

const pwd = document.getElementById('password');
const pwdverif = document.getElementById('passwordverif');

function validatePassword() {
    if (pwd.value !== pwdverif.value) {
        pwdverif.setCustomValidity('Les mots de passe ne correspondent pas');
    } else {
        pwdverif.setCustomValidity('');
    }
}

pwd.addEventListener('change', validatePassword);
pwdverif.addEventListener('input', validatePassword);

// Apply immediately on page load (case of reload after POST)
toggleStudentFields();

toggleClientFields();

// Apply on each status change
userTypeSelect.addEventListener('change', toggleStudentFields);
