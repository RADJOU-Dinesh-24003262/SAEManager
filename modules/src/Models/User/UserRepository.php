<?php

namespace Models\User;

use includes\database;

/**
 * Class UserRepository

 * @package src

 * @subpackage Models\User

 * @author Benhafessa Alexandre, Dargentolle Francois, Edelstein William, Griguer Nathan, Radjou Dinesh

 * This class regroup function to manage the users in the database
 */
class UserRepository
{
    /**
     * Connection to the database, storred in this database object
     *
     * @var database
     */
    private database $db;

    /**
     * Creates an instance of the class
     *
     * This method constructs a user repository object, affecting the database given in parametters
     * to the db variable.
     *
     * @param database $db The database to instanciate
     */
    public function __construct(database $db)
    {
        $this->db = $db;
    }

    /**
     * Returns the existance of the given email
     *
     * Looks for the email given in parametters in the database,
     * return true if the email exists. False if it is not found
     * or if an error occurs.
     *
     * @param string $email The demail to verify in the database
     *
     * @return boolean
     */
    public function existsByEmail(string $email): bool
    {
        try {
            $stmt = $this->db->prepare("SELECT COUNT(*) FROM users WHERE email = :email");
            $stmt->execute(['email' => $email]);
            return $stmt->fetchColumn() > 0;
        } catch (\PDOException $e) {
            error_log("Erreur vérification email: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Returns the success of th insertion of a user in the database
     *
     * Tries to insert a new user in the database with the user given in parametters.
     * Return true if it succeed, false otherwise.
     *
     * @param User $user The user object to insert
     *
     * @return boolean
     */
    public function save(User $user): bool
    {
        try {
            $stmt = $this->db->prepare(
                "
                INSERT INTO users (amu_id, first_name, last_name, user_type, 
                                   email, password, phone, date_of_birth, city, 
                                   year, parcours, td, tp)
                VALUES (:amu_id, :first_name, :last_name, :user_type,
                        :email, :password, :phone, :dob, :city,
                        :year, :parcours, :td, :tp)
            "
            );

            return $stmt->execute(
                [
                'amu_id' => $user->getAmuId(),
                'first_name' => $user->getFirstName(),
                'last_name' => $user->getLastName(),
                'user_type' => $user->getUserType(),
                'email' => $user->getEmail(),
                'password' => $user->getPasswordHash(),
                'phone' => $user->getPhone(),
                'dob' => $user->getDateOfBirth(),
                'city' => $user->getCity(),
                'year' => $user->getYear(),
                'parcours' => $user->getParcours(),
                'td' => $user->getTd(),
                'tp' => $user->getTp()
                ]
            );
        } catch (\PDOException $e) {
            error_log("Erreur sauvegarde utilisateur: " . $e->getMessage());
            return false;
        }
    }
}
