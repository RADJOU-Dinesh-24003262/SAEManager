<?php
    require 'data-processing.php';
    registration();
?>

<?php
    require '../utilis/inc.php';
    start_page('Inscription SAEManager');
?>

        <form action="register/" method="post" autocomplete="on">
            <label for="id">Identifiant AMU :</label>
            <input required type="text" id="id" name="id" minlength="3" maxlength="30" pattern="[a-zA-Z0-9._-]+" title="L'identifiant doit contenir uniquement lettres, chiffres, points, tirets ou underscores"><br>

            <p>Civilité :</p>
            <input required type="radio" id="male" name="gender" value="male">
            <label for="male">Male</label>
            <input type="radio" id="female" name="gender" value="female">
            <label for="female">Female</label>
            <input type="radio" id="other" name="gender" value="other">
            <label for="other">Other</label><br>

            <label for="fname">Prénom :</label>
            <input required type="text" id="fname" name="fname" pattern="[A-Za-zÀ-ÖØ-öø-ÿ\-']+" title="Lettres, tirets et apostrophes seulement"><br>

            <label for="lname">Nom :</label>
            <input required type="text" id="lname" name="lname" pattern="[A-Za-zÀ-ÖØ-öø-ÿ\-']+" title="Lettres, tirets et apostrophes seulement"><br>

            <label for="email">E-mail AMU :</label>
            <input required autocomplete="email" type="email" id="email" name="email" pattern="^[a-zA-ZÀ-ÿ\-']+\.[a-zA-ZÀ-ÿ\-']+(\.[0-9]+)?@(etu\.)?univ-amu\.fr$" title="Format : prenom.nom@etu.univ-amu.fr ou prenom.nom.123@etu.univ-amu.fr"><br>

            <label for="pwd">Mot de passe :</label>
            <input required type="password" id="pwd" name="pwd" minlength="8" title="Au moins 8 caractères"><br>

            <label for="pwdverif">Vérification de Mot de passe :</label>
            <input required type="password" id="pwdverif" name="pwdverif" minlength="8"><br>

            <label for="tel">Téléphone Français :</label>
            <input required autocomplete="tel" type="tel" id="tel" name="tel" pattern="0[4,6,7][0-9]{8}" maxlength="10" title="Format : 06XXXXXXXX ou 07XXXXXXXX ou 04XXXXXXXX"><br>

            <label for="dob">Date de naissance :</label>
            <input required type="date" id="dob" name="dob" min="1940-01-01" max="<?php echo date('Y-m-d', strtotime('-16 years')); ?>"><br>

            <label for="city">Ville :</label>
            <input required type="text" id="city" name="city" pattern="[A-Za-zÀ-ÖØ-öø-ÿ\- ]+" title="Lettres, espaces et tirets seulement"><br>

            <label for="year">Année de BUT :</label>
            <select id="year" name="year" required>
                <option value="">-- Sélectionner --</option>
                <option value="1">BUT 1 (1ère année)</option>
                <option value="2">BUT 2 (2ème année)</option>
                <option value="3">BUT 3 (3ème année)</option>
            </select><br>

            <label for="parcours">Parcours :</label>
            <select disabled id="parcours" name="parcours" required>
                <option value="">-- Sélectionner --</option>
                <option value="A">parcours A</option>
                <option value="B">parcours B</option>
            </select><br>

            <label for="td">Groupe de TD :</label>
            <select id="td" name="td" required>
                <option value="">-- Sélectionner --</option>
                <option value="TD1">TD1</option>
                <option value="TD2">TD2</option>
                <option value="TD3">TD3</option>
                <option disabled value="TD4">TD4</option>
            </select><br>

            <label for="tp">Groupe de TP :</label>
            <select id="tp" name="tp" required>
                <option value="">-- Sélectionner --</option>
                <option value="TPA">TP A</option>
                <option value="TPB">TP B</option>
            </select><br>

            <label for="terms">
                <input required type="checkbox" id="terms" name="terms">
                Accepter les conditions générales
            </label><br>

            <button type="submit" name="action" value="registration">S'inscrire</button>
        </form>


        <script>
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
        </script>

<?php
    end_page();
?>


