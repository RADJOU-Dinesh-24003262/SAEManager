<?php
    require '../utilis/validator.php';
    
    
    function registration() : void{
        if ($_SERVER['REQUEST_METHOD'] === 'POST'){
            $action = $_POST['action'];

            if($action === 'registration'){
                $key_tab = ['id', 'fname', 'lname', 'gender', 'email', 'pwd', 'pwdverif', 'tel', 'dob', 'city', 'year', 'parcours', 'td', 'tp', 'terms'];

                foreach ($key_tab as $key_value) {
                    if (!isset($_POST[$key_value]) || empty($_POST[$key_value])) {
                        //Parcours n'est pas obligatoire en 1ère année
                        if($key_value === 'parcours' && $_POST['year'] === '1') {continue;}
                        echo "<script>alert(\"Le champ $key_value est requis.\");</script>";
                        return;
                    }

                    $value = valid_donnees($_POST[$key_value]);

                    switch ($key_value) {
                        case 'gender':
                            if (!isValidGender($value)) {
                                echo "<script>alert(\"La civilité n'est pas valide.\");</script>";
                                return;
                            }
                            break;

                        case 'email':
                            if (!isValidEmail($value)) {
                                echo "<script>alert(\"L'adresse e-mail n'est pas valide.\");</script>";
                                return;
                            }if(!isOwnEmail($value, $lname, $fname)) {
                                echo "<script>alert(\"Utilisez votre adresse e-mail.\");</script>";
                                return;
                            }
                            break;
                        
                        case 'pwd' :
                            if (!isValidPassword($value)) {
                                echo "<script>alert(\"Le mot de passe n'est pas valide.\");</script>";
                                return;
                            }if (!isPasswordMatch($value, $_POST['pwdverif'])) {
                                echo "<script>alert(\"Les mots de passe ne correspondent pas.\");</script>";
                                return;
                            }
                            break;

                        case 'tel':
                            if (!isValidPhone($value)) {
                                echo "<script>alert(\"Le numéro de téléphone n'est pas valide.\");</script>";
                                return;
                            }
                            break;
                        
                        case 'dob' :
                            if(isValidDate($value)) {
                                // Vérifier si l'utilisateur a au moins 16 ans
                                $today = new DateTime();
                                $dob = new DateTime($value);
                                $age = $today->diff($dob)->y;
                                if ($age < 16) {
                                    echo "<script>alert(\"Vous devez avoir au moins 16 ans pour vous inscrire.\");</script>";
                                    return;
                                }
                            } else {
                                echo "<script>alert(\"La date de naissance n'est pas valide.\");</script>";
                                return;
                            }
                            break;
                            
                        case 'year':
                            if (!isValidYear($value)) {
                                echo "<script>alert(\"L'année n'est pas valide.\");</script>";
                                return;
                            }
                            break;
                        
                        case 'parcours':
                            echo $year;
                            echo $value;
                            echo '<br>';
                            if ($year === '1' && !empty($value)) {
                                echo "<script>alert(\"Le parcours ne doit pas être sélectionné en 1ère année.\");</script>";
                                return;
                            }

                            if (in_array($year, ['2', '3'])) {
                                if (empty($value)) {
                                    echo "<script>alert(\"Le parcours doit être sélectionné en 2ème ou 3ème année.\");</script>";
                                    return;
                                }
                                if (!isValidParcours($value)) {
                                    echo "<script>alert(\"Le parcours sélectionné n'est pas valide.\");</script>";
                                    return;
                                }
                            }
                            break;

                        case 'td':
                            if (!isValidTD($value)) {
                                echo "<script>alert(\"Le groupe de TD n'est pas valide.\");</script>";
                                return;
                            }elseif(($year === '2' || $year === '3') && $value === 'TD4') {
                                echo "<script>alert(\"Le groupe de TD4 ne peut pas être sélectionné en 2ème ou 3ème année.\");</script>";
                                return;
                            }
                            break;

                        case 'tp':
                            if (!isValidTP($value)) {
                                echo "<script>alert(\"Le groupe de TP n'est pas valide.\");</script>";
                                return;
                            }
                            break;
                            
                        case 'terms':
                            if ($value !== 'on') {
                                echo "<script>alert(\"Vous devez accepter les conditions générales.\");</script>";
                                return;
                            }
                            break;
                    }

                    ${$key_value} = $value;
                }
            }
            else{
                echo '<br><strong>Bouton non géré !</strong><br>';
                echo $action;
            }
        }
    }    
?>