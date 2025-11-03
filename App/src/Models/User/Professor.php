<?php

namespace Models\User;

use PDO;

/**
 * Représente un utilisateur de type professeur dans le système.
 *
 * Fournit les opérations spécifiques aux professeurs (sauvegarde, récupération
 * de données spécifiques, gestion des SAE et des groupes).
 *
 * @category Models
 * @package  Src
 * @subpackage Models\User
 * @author   Alexandre Benhafessa <alexandre.benhafessa@etu.univ-amu.fr>
 * @author   François Dargentolle <francois.dargentolle@etu.univ-amu.fr>
 * @author   William Edelstein <william.edelstein@etu.univ-amu.fr>
 * @author   Nathan Griguer <nathan.griguer@etu.univ-amu.fr>
 * @author   Dinesh Radjou <dinesh.radjou@etu.univ-amu.fr>
 * @license  MIT License https://opensource.org/licenses/MIT
 * @link     https://github.com/RADJOU-Dinesh-24003262/SAEManager
 */
class Professor extends User
{
    /**
     * Identifiant AMU du professeur.
     *
     * @var string
     */
    protected string $amu_id = '';

    /**
     * Initialise un nouveau professeur.
     *
     * Définit le type d'utilisateur et délègue l'initialisation au constructeur
     * parent (User).
     *
     * @param array $data Données initiales facultatives du professeur.
     */
    public function __construct(array $data = [])
    {
        $this->user_type = 'professor';
        parent::__construct($data);
    }

    /**
     * Sauvegarde les données spécifiques au professeur dans la base de données.
     *
     * Insère une ligne dans la table `professors`.
     *
     * @param PDO $connection Objet PDO représentant la connexion à la base.
     * @param int $userId     Identifiant de l'utilisateur dans la table `users`.
     *
     * @return void
     *
     * @throws \PDOException En cas d'erreur lors de l'exécution de la requête.
     */
    protected function saveSpecificData(PDO $connection, int $userId): void
    {
        $stmt = $connection->prepare(
            'INSERT INTO professors (professor_id, amu_id)
             VALUES (:professor_id, :amu_id)'
        );

        $stmt->execute(
            [
                'professor_id' => $userId,
                'amu_id' => $this->amu_id,
            ]
        );
    }

    /**
     * Récupère les données spécifiques au professeur depuis la base de données.
     *
     * Remplit les propriétés de l'objet correspondant aux colonnes récupérées.
     *
     * @param PDO    $db    Objet PDO représentant la connexion à la base.
     * @param string $email Adresse e-mail de l'utilisateur liée au professeur.
     *
     * @return void
     *
     * @throws \PDOException En cas d'erreur lors de l'exécution de la requête.
     */
    protected function fetchSpecificData(PDO $db, string $email): void
    {
        $stmt = $db->prepare(
            'SELECT p.*
             FROM professors p
             JOIN users u ON p.professor_id = u.user_id
             WHERE u.email = :email'
        );

        $stmt->execute(['email' => $email]);
        $data = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($data) {
            foreach ($data as $key => $value) {
                if (property_exists($this, $key)) {
                    $this->$key = $value;
                }
            }
        }
    }

    /**
     * Récupère les SAE associés au professeur.
     *
     * @param PDO $connection Objet PDO représentant la connexion à la base.
     * @param int $userId     Identifiant du professeur (professor_id).
     *
     * @return array Tableau associatif contenant les enregistrements des SAE.
     *
     * @throws \PDOException En cas d'erreur lors de l'exécution de la requête.
     */
    protected function fetchSAEData(PDO $connection, int $userId): Array
    {
        $stmt = $connection->prepare('SELECT * FROM SAE_subjects
                                            JOIN sae_professor_groups on SAE_subjects.sae_subject_id = sae_professor_groups.sae_subject_id
                                            JOIN professors ON sae_professor_groups.professor_id = professors.professor_id
                                            WHERE professors.professor_id = :professor_id');
        $stmt->execute(['professor_id' => $userId]);
        $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
        return $data;
    }

    /**
     * Crée un nouvel enregistrement SAE (subject) lié au professeur.
     *
     * Les dates sont converties avec STR_TO_DATE selon le format attendu.
     *
     * @param PDO    $connection Objet PDO représentant la connexion à la base.
     * @param int    $clientid   Identifiant du client lié à la SAE.
     * @param string $name       Nom de la SAE.
     * @param string $begindate  Date de début (format attendu: ex. "March 01 2024").
     * @param string $enddate    Date de fin (format attendu: ex. "June 30 2024").
     * @param int    $userId     Identifiant du professeur responsable.
     *
     * @return void
     *
     * @throws \PDOException En cas d'erreur lors de l'exécution de la requête.
     */
    protected function createSAE(PDO $connection, int $clientid, string $name, string $begindate, string $enddate, int $userId): void {
        $stmt = $connection->prepare(
            'INSERT INTO sae_subjects(responsible_prof_id, client_id, subject_name, begin_date, end_date)
            VALUES
            (:responsible_prof_id, :client_id, :subject_name, STR_TO_DATE(:begin_date, "%M %d %Y"), STR_TO_DATE(:end_date, "%M %d %Y"));'
        );
        $stmt->execute(
            [
                'responsible_prof_id' => $userId,
                'client_id' => $clientid,
                'subject_name' => $name,
                'begin_date' => $begindate,
                'end_date' => $enddate
            ]
        );
    }

    /**
     * Met à jour une SAE existante avec les données fournies.
     *
     * Si une valeur du tableau \$data est NULL, la valeur courante du SAE est conservée.
     *
     * @param PDO   $connection Objet PDO représentant la connexion à la base.
     * @param \SAE  $sae        Instance de SAE représentant l'enregistrement courant.
     * @param array $data       Tableau associatif des champs à mettre à jour.
     *                          Clés attendues : 'responsible_prof_id', 'client_id',
     *                          'subject_name', 'begin_date', 'end_date'.
     *
     * @return void
     *
     * @throws \PDOException En cas d'erreur lors de l'exécution de la requête.
     */
    protected function updateSAE(PDO $connection, \SAE $sae, array $data): void {
        $saedata = $sae->getDataArray();
        for($i=0;$i<count($data);$i++){
            if($data[$i] == Null){
                $data[$i] = $saedata[$i];
            }
        }
        $stmt = $connection->prepare('UPDATE sae_subjects
                                    SET sae_subject_id = sae_subject_id, responsible_prof_id = :responsible_prof_id, client_id = :client_id,
                                        subject_name = :subject_name, begin_date = :begin_date, end_date = :end_date
                                    WHERE sae_subject_id = :sae_subject_id;');
        $stmt->execute(
            [
                'sae_subject_id' => $sae->getSaeSubjectId(),
                'responsible_prof_id' => $data['responsible_prof_id'],
                'client_id' => $data['client_id'],
                'subject_name' => $data['subject_name'],
                'begin_date' => $data['begin_date'],
                'end_date' => $data['end_date']
            ]
        );
    }

    /**
     * Crée un groupe SAE pour un sujet donné et retourne son identifiant.
     *
     * Insère d'abord un enregistrement dans `SAE_groups`, puis sélectionne un
     * groupe non attribué aux étudiants pour le même SAE.
     *
     * @param PDO $connection Objet PDO représentant la connexion à la base.
     * @param int $saeID      Identifiant du sujet SAE.
     *
     * @return int Identifiant du groupe créé (sae_group_id).
     *
     * @throws \PDOException En cas d'erreur lors de l'exécution des requêtes.
     */
    protected function createGroup(PDO $connection, int $saeID): int {
        $stmt = $connection->prepare('INSERT INTO SAE_groups(sae_subject_id)
                                    VALUES (:sae_subject_id);');
        $stmt->execute(
            [
                'sae_subject_id' => $saeID
            ]
        );
        $stmt = $connection->prepare('SELECT sae_group_id FROM SAE_groups
                                    WHERE sae_group_id NOT IN (SELECT sae_group_id FROM students)
                                    AND sae_subject_id = :sae_subject_id
                                    LIMIT 1;');
        $stmt->execute(
            [
                'sae_subject_id' => $saeID
            ]
        );
        return (int)$stmt->fetchColumn(0);
    }

    /**
     * Assigne un étudiant à un groupe SAE.
     *
     * @param PDO $connection Objet PDO représentant la connexion à la base.
     * @param int $groupId    Identifiant du groupe SAE.
     * @param int $student    Identifiant de l'étudiant (student_id).
     *
     * @return void
     *
     * @throws \PDOException En cas d'erreur lors de l'exécution de la requête.
     */
    protected function assignedSAE(PDO $connection, int $groupId, int $student): void {
        $stmt = $connection->prepare('UPDATE students
                                    SET sae_group_id = :sae_group_id
                                    WHERE student_id = :student_id;');
        $stmt->execute(
            [
                'sae_group_id' => $groupId,
                'student_id' => $student
            ]
        );
    }

    /**
     * Retire l'association d'un professeur à une SAE.
     *
     * Supprime la ligne correspondante dans `sae_professor_groups`.
     *
     * @param PDO $connection Objet PDO représentant la connexion à la base.
     * @param int $saeID      Identifiant du sujet SAE.
     * @param int $profID     Identifiant du professeur.
     *
     * @return void
     *
     * @throws \PDOException En cas d'erreur lors de l'exécution de la requête.
     */
    protected function removeProfFromSae(PDO $connection, int $saeID, int $profID): void {
        $stmt = $connection->prepare('DELETE FROM sae_professor_groups
                                    WHERE sae_subject_id = :sae_subject_id
                                    AND professor_id = :professor_id;');
        $stmt->execute(
            [
                'sae_subject_id' => $saeID,
                'professor_id' => $profID
            ]
        );
    }

    /**
     * Ajoute l'association d'un professeur à une SAE.
     *
     * @param PDO $connection Objet PDO représentant la connexion à la base.
     * @param int $saeID      Identifiant du sujet SAE.
     * @param int $profID     Identifiant du professeur.
     *
     * @return void
     *
     * @throws \PDOException En cas d'erreur lors de l'exécution de la requête.
     */
    protected function addProfToSae(PDO $connection, int $saeID, int $profID): void {
        $stmt = $connection->prepare('INSERT INTO sae_professor_groups(sae_subject_id, professor_id)
                                    VALUES (:sae_subject_id, :professor_id);');
        $stmt->execute(
            [
                'sae_subject_id' => $saeID,
                'professor_id' => $profID
            ]
        );
    }

    /**
     * Désaffecte un étudiant d'une SAE (retire l'étudiant du groupe).
     *
     * @param PDO  $connection Objet PDO représentant la connexion à la base.
     * @param \SAE $sae        Instance de SAE utilisée pour récupérer l'id.
     * @param int  $student    Identifiant de l'étudiant à désaffecter.
     *
     * @return void
     *
     * @throws \PDOException En cas d'erreur lors de l'exécution de la requête.
     */
    protected function unassignedSAE(PDO $connection, \SAE $sae, int $student): void {
        $stmt = $connection->prepare('UPDATE students
                                    SET sae_group_id = :sae_subject_id
                                    WHERE student_id = :student_id;');
        $stmt->execute(
            [
                'sae_group_id' => $sae->getSaeSubjectId(),
                'student_id' => Null
            ]
        );
    }

    // -----------------
    // Getters
    // -----------------

    /**
     * Retourne l'identifiant AMU du professeur.
     *
     * @return string Identifiant AMU.
     */
    protected function getAmuId(): string
    {
        return $this->amu_id;
    }
}