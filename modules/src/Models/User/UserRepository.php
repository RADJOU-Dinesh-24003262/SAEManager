<?php
namespace Models\User;

use includes\database;

class UserRepository
{
    private database $db;

    public function __construct(database $db)
    {
        $this->db = $db;
    }
    
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
    
    public function save(User $user): bool
    {
        try {
            $stmt = $this->db->prepare("
                INSERT INTO users (amu_id, first_name, last_name, user_type, 
                                   email, password, phone, date_of_birth, city, 
                                   year, parcours, td, tp)
                VALUES (:amu_id, :first_name, :last_name, :user_type,
                        :email, :password, :phone, :dob, :city,
                        :year, :parcours, :td, :tp)
            ");
            
            return $stmt->execute([
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
            ]);
        } catch (\PDOException $e) {
            error_log("Erreur sauvegarde utilisateur: " . $e->getMessage());
            return false;
        }
    }
}