const yearSelect = document.getElementById('year');
const tdSelect = document.getElementById('td');
const majorSelect = document.getElementById('major');

// Disable TD4 if year is 2 or 3
const td4Option = Array.from(tdSelect.options).find(opt => opt.value === 'TD4');

yearSelect.addEventListener('change', () => {
    if (yearSelect.value === '2' || yearSelect.value === '3') {
        td4Option.disabled = true;
        majorSelect.disabled = false;
        if (tdSelect.value === 'TD4') {
            tdSelect.value = '';
        }
    } else {
        td4Option.disabled = false;
        majorSelect.disabled = true;
        majorSelect.value = '';
    }
});

// Disable fields for teachers and partners
const userTypeSelect = document.getElementById('userType');
const tpSelect = document.getElementById('tp');
const etudiantFields = document.getElementById('etudiantFields');
const amuId = document.getElementById('id').parentElement;

// Client fields
const clientFields = document.getElementById('clientFields');
const organisationInput = document.getElementById('organisation');

function toggleStudentFields() {
    const isStudent = userTypeSelect.value === 'student';

    etudiantFields.style.display = isStudent ? 'block' : 'none';
    majorSelect.disabled = !isStudent;
    yearSelect.disabled = !isStudent;
    tdSelect.disabled = !isStudent;
    tpSelect.disabled = !isStudent;

    if (!isStudent) {
        majorSelect.value = '';
        yearSelect.value = '';
        tdSelect.value = '';
        tpSelect.value = '';
    }
}

function toggleClientFields() {
    const isClient = userTypeSelect.value === 'client';

    clientFields.style.display = isClient ? 'block' : 'none';
    organisationInput.required = isClient;
    organisationInput.disabled = !isClient;

    if (!isClient) {
        organisationInput.value = '';
    }
}


function toggleAmuFields(){
    const isAmu = (userTypeSelect.value === 'student' || userTypeSelect.value === 'professor');
    const amuIdLabel = document.querySelector('label[for="id"]');
    const amuIdInput = document.getElementById('id');
    const amuIdHint = document.getElementById('id-hint');

    if (isAmu) {
        amuIdLabel.style.display = 'block';
        amuIdInput.style.display = 'block';
        amuIdHint.style.display = 'block';
        amuIdInput.required = true;
        amuIdInput.disabled = false;
    } else {
        amuIdLabel.style.display = 'none';
        amuIdInput.style.display = 'none';
        amuIdHint.style.display = 'none';
        amuIdInput.required = false;
        amuIdInput.value = '';
        amuIdInput.disabled = true;
    }

}



function toggleAmuMailParts(){

    const userType = userTypeSelect.value;

    const mailHint = document.getElementById('email-hint');

    mailHint.style.display = 'none';

    if (userType === 'student') {
        mailHint.style.display = 'inline-block';
    } else if (userType === 'professor') {
        mailHint.style.display = 'inline-block';
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

userTypeSelect.addEventListener('change', () => {
    toggleStudentFields();
    toggleAmuFields();
    toggleAmuMailParts();
    toggleClientFields();

});

// Apply immediately on page load (case of reload after POST)
toggleStudentFields();
toggleAmuFields();
toggleAmuMailParts();
toggleClientFields(); 
