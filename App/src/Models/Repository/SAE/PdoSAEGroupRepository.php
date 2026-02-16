<?php

namespace Models\Repository\SAE;

use Core\Models\Repository\BaseRepository;
use Models\Entity\SAE\SAEGroup;
use Models\UseCase\SAE\InterfaceDB\SAEGroupInterface;
use Override;
use PDO;
use PDOException;

/**
 * PDO implementation of SAEGroupInterface.
 *
 * This is the Infrastructure layer implementation of the Interface.
 *
 * @category   Models
 * @package    Src
 * @subpackage Models/Repository/SAE
 * @author     Dinesh Radjou <dinesh.radjou@etu.univ-amu.fr>
 * @license    MIT License https://opensource.org/licenses/MIT
 * @link       https://github.com/RADJOU-Dinesh-24003262/SAEManager
 *
 * @extends BaseRepository<SAEGroup>
 */
class PdoSAEGroupRepository extends BaseRepository implements SAEGroupInterface
{
    /**
     * The singleton instance.
     * @var PdoSAEGroupRepository|null
     */
    protected static ?PdoSAEGroupRepository $instance = null;

    /**
     * The table name.
     *
     * @var string
     */
    protected string $table = 'sae_groups';

    /**
     * The entity class name.
     *
     * @var class-string<SAEGroup>
     */
    protected string $entityClass = SAEGroup::class;

    /**
     * Constructor.
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Returns the name of the primary key.
     *
     * @return string
     */
    #[Override]
    protected function getPrimaryKey(): string
    {
        return 'sae_group_id';
    }

    /**
     * Finds a SAE group by ID.
     *
     * @param integer $id The SAE group ID.
     * @return SAEGroup|null
     */
    #[Override]
    public function findById(int $id): ?SAEGroup
    {
        return parent::findById($id);
    }

    /**
     * Finds all groups for a SAE subject.
     *
     * @param integer $saeSubjectId The SAE subject ID.
     * @return array<SAEGroup>
     */
    #[Override]
    public function findBySaeSubjectId(int $saeSubjectId): array
    {
        try {
            $stmt = $this->connection->prepare(
                'SELECT * FROM sae_groups WHERE sae_subject_id = :sae_id ORDER BY sae_group_id'
            );
            $stmt->execute(['sae_id' => $saeSubjectId]);
            $data = $stmt->fetchAll(PDO::FETCH_ASSOC);

            return array_map(fn ($row) => new SAEGroup($row), $data);
        } catch (PDOException $e) {
            error_log('Erreur récupération groupes : ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Creates a new SAE group.
     *
     * @param SAEGroup $entity The SAE group to create.
     * @return integer|boolean The created group with ID or false on failure.
     */
    #[Override]
    public function insert(object $entity): int|bool
    {
        try {
            $this->connection->beginTransaction();

            $stmt = $this->connection->prepare(
                'INSERT INTO sae_groups (sae_subject_id, professor_id) 
                 VALUES (:sae_subject_id, :professor_id) 
                 RETURNING sae_group_id'
            );

            $stmt->execute([
                'sae_subject_id' => $entity->getSaeSubjectId(),
                'professor_id' => $entity->getProfessorId()
            ]);

            $id = intval($stmt->fetchColumn());
            $stmt->closeCursor();

            $this->connection->commit();
            return $id;
        } catch (PDOException $e) {
            $this->connection->rollBack();
            error_log('Error creating SAE group: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Updates a SAE group.
     *
     * @param SAEGroup $entity The SAE group to update.
     * @return boolean True on success.
     */
    #[Override]
    public function update(object $entity): bool
    {
        try {
            $stmt = $this->connection->prepare(
                'UPDATE sae_groups 
                 SET sae_subject_id = :sae_id, professor_id = :prof_id 
                 WHERE sae_group_id = :id'
            );
            return $stmt->execute([
                'sae_id' => $entity->getSaeSubjectId(),
                'prof_id' => $entity->getProfessorId(),
                'id' => $entity->getSaeGroupId()
            ]);
        } catch (PDOException $e) {
            error_log('Erreur mise à jour groupe : ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Deletes a SAE group.
     *
     * @param integer $id The SAE group ID.
     * @return boolean True on success.
     */
    #[Override]
    public function delete(int $id): bool
    {
        return parent::delete($id);
    }

    /**
     * Finds all SAE groups.
     *
     * @return array<SAEGroup>
     */
    #[Override]
    public function findAll(): array
    {
        return parent::findAll();
    }

    /**
     * Gets students in a group with their details.
     *
     * @param integer $groupId The SAE group ID.
     * @return array<int, array{
     *   student_id: string,
     *   amu_id: string,
     *   year: string,
     *   td: string,
     *   tp: string,
     *   first_name: string,
     *   last_name: string,
     *   email: string,
     *   phone: string|null
     * }>
     */
    #[Override]
    public function getStudentsInGroup(int $groupId): array
    {
        try {
            $stmt = $this->connection->prepare(
                'SELECT 
                    s.student_id, s.amu_id, s.year, s.td, s.tp,
                    u.first_name, u.last_name, u.email, u.phone
                FROM students s
                JOIN users u ON s.student_id = u.user_id
                JOIN participated_in pi ON s.student_id = pi.student_id
                WHERE pi.sae_group_id = :group_id
                ORDER BY u.last_name, u.first_name'
            );
            $stmt->execute(['group_id' => $groupId]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log('Erreur récupération étudiants du groupe : ' . $e->getMessage());
            return [];
        }
    }
}