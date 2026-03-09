<?php

namespace Views\ToDoList;

use Models\Entity\SAE\SAESubject;
use Models\Entity\User\User;
use Override;
use Views\BaseSaeView;
use Core\Utils\SessionService;
use Models\Entity\ToDoItem\ToDoItem;

/**
 * Class ToDoListView
 *
 * Represents the view for the "To-Do List" page of the SAE Manager application.
 * This view is responsible for displaying the to-do list of the students in a specific SAE.
 * It extends {@see BaseSaeView} and provides specific implementations
 * for rendering the To-Do List page, including the associated CSS file,
 * template path, and metadata headers.
 *
 * @category   View
 * @package    Src
 * @subpackage Views/ToDoList
 * @author     Alexandre Benhafessa <alexandre.benhafessa@etu.univ-amu.fr>
 * @author     François Dargentolle <francois.dargentolle@etu.univ-amu.fr>
 * @author     William Edelstein <william.edelstein@etu.univ-amu.fr>
 * @author     Nathan Griguer <nathan.griguer@etu.univ-amu.fr>
 * @author     Dinesh Radjou <dinesh.radjou@etu.univ-amu.fr>
 * @license    MIT License https://opensource.org/licenses/MIT
 * @link       https://github.com/RADJOU-Dinesh-24003262/SAEManager
 */
class ToDoListView extends BaseSaeView
{
    private const TEMPLATE_HTML = __DIR__ . '/to-do-list.html';


    /**
     * @var integer|null The current group ID.
     */
    protected ?int $currentGroupId;

    /**
     * @var array<ToDoItem> The list of tasks.
     */
    protected $tasks;

    /**
     * @var array<int, array{
     *      group: \Models\Entity\SAE\SAEGroup,
     *      students: array<int, array<string, string|null>>
     * }> The list of all groups.
     */
    protected array $allGroups;


    // -------------------------------------------------------------------------
    // Constructor
    // -------------------------------------------------------------------------

    /**
     * Constructs a new ToDoListView instance.
     *
     * @param SAESubject           $subject        The SAE subject details.
     * @param integer|null         $currentGroupId The ID of the currently selected group,
     *                                             or null if no group is selected.
     * @param array<int, ToDoItem> $tasks          The list of ToDoItem objects for the selected group.
     * @param array                $allGroups      An array containing details of all SAE groups.
     * @phpstan-param array<int, array{
     *     group: \Models\Entity\SAE\SAEGroup,
     *     students: array<int, array<string, string|null>>
     * }> $allGroups
     * @param User                 $user           The current user object.
     */
    public function __construct(
        SAESubject $subject,
        ?int $currentGroupId,
        array $tasks,
        array $allGroups,
        User $user
    ) {
        parent::__construct($subject, $user);

        $this->currentGroupId = $currentGroupId;
        $this->tasks = $tasks;
        $this->allGroups = $allGroups;
    }

    /**
     * Returns the path to the HTML template file.
     *
     * @return string The full path to the HTML template.
     */
    #[Override]
    protected function templatePath(): string
    {
        return self::TEMPLATE_HTML;
    }

    /**
     * Returns an associative array of keys and values used in the HTML template.
     *
     * This method returns an empty array because the To-Do List page
     * does not require dynamic data to render.
     *
     * @return array<string, string|integer|null> An empty associative array.
     */
    #[Override]
    protected function templateKeys(): array
    {
        $errors = $this->data['errors'] ?? [];

        return array_merge(
            $this->getCommonSaeTemplateKeys(),
            [
                'ERROR_MESSAGES' => $this->renderErrorMessages($errors),
                'TODO_CONTENT'   => $this->renderTodoContent(),
            ]
        );
    }

    /**
     * Renders the main content of the todo page: either the list of groups (for professors)
     * or the task board (for students or selected group).
     *
     * @return string HTML content.
     */
    private function renderTodoContent(): string
    {
        $currentGroupId = $this->currentGroupId;

        // If a group is selected (Student or Professor who selected a group).
        if ($currentGroupId) {
            return $this->renderTaskBoard($currentGroupId, $this->tasks);
        }

        // If professor and no group selected.
        if ($this->user->isProfessor()) {
            return $this->renderGroupList($this->allGroups);
        }

        return '<p>Aucun groupe assigné pour le moment.</p>';
    }

    /**
     * Renders the list of groups for a professor to choose from.
     *
     * @param array<int, mixed> $groups List of group data.
     * @return string HTML content.
     */
    private function renderGroupList(array $groups): string
    {
        $html = '<h2>Suivi des Groupes</h2>';
        $html .= '<p>Sélectionnez un groupe pour voir son avancement :</p>';
        $html .= '<div class="group-list-container" style="display: flex; gap: 1rem; flex-wrap: wrap;">';

        if (empty($groups)) {
            $html .= '<p>Aucun groupe disponible.</p>';
        } else {
            foreach ($groups as $groupData) {
                $group = $groupData['group'];
                $groupId = $group->getSaeGroupId();
                $saeId = $this->subject->getSaeSubjectId();

                $html .= '<a href="/sae/' . $saeId . '/to-do?group_id=' . $groupId . '" class="btn btn-secondary" ';
                $html .= 'style="padding: 20px; border: 1px solid #ccc; text-decoration: none; ';
                $html .= 'color: inherit; display: block; border-radius: 8px;">';
                $html .= '<h3>Groupe ' . $groupId . '</h3>';
                $html .= '</a>';
            }
        }
        $html .= '</div>';
        return $html;
    }

    /**
     * Renders the task board for a specific group.
     *
     * @param integer              $groupId The group ID.
     * @param array<int, ToDoItem> $tasks   The list of tasks.
     * @return string HTML content.
     */
    private function renderTaskBoard(int $groupId, array $tasks): string
    {
        $html = '<h2>Tableau de bord - Groupe ' . $groupId . '</h2>';

        // Add a "Back to groups" button for professors.
        if ($this->user->isProfessor()) {
            $saeId = $this->subject->getSaeSubjectId();
            $html .= '<a href="/sae/' . $saeId . '/to-do" class="btn btn-secondary" ';
            $html .= 'style="margin-bottom: 1rem; display: inline-block;">&larr; Retour aux groupes</a>';
        }

        $pendingTasks = [];
        $completedTasks = [];

        foreach ($tasks as $task) {
            if ($task->isChecked()) {
                $completedTasks[] = $task;
            } else {
                $pendingTasks[] = $task;
            }
        }

        $html .= '<div class="tasks-container">';

        // Pending Tasks.
        $html .= '<div class="tasks-column pending">';
        $html .= '<h3>Tâches en cours</h3>';
        $html .= '<ul id="pending-list" class="task-list">';
        if (empty($pendingTasks)) {
            $html .= '<li class="empty-message">Aucune tâche en cours.</li>';
        } else {
            foreach ($pendingTasks as $task) {
                $html .= $this->renderTaskItem($task);
            }
        }
        $html .= '</ul>';
        $html .= '</div>';

        // Completed Tasks.
        $html .= '<div class="tasks-column completed">';
        $html .= '<h3>Tâches terminées</h3>';
        $html .= '<ul id="completed-list" class="task-list">';
        if (empty($completedTasks)) {
            $html .= '<li class="empty-message">Aucune tâche terminée.</li>';
        } else {
            foreach ($completedTasks as $task) {
                $html .= $this->renderTaskItem($task);
            }
        }
        $html .= '</ul>';
        $html .= '</div>';

        $html .= '</div>'; // End tasks-container.

        // Add form for students ONLY to add tasks.
        if ($this->user->isStudent()) {
            $html .= '<div class="add-task-form">';
            $html .= '<h3>Ajouter une tâche</h3>';
            $html .= '<input type="text" id="new-task-input" placeholder="Nouvelle tâche...">';
            $html .= '<select id="new-task-priority">';
            $html .= '<option value="1">Haute</option>';
            $html .= '<option value="2" selected>Moyenne</option>';
            $html .= '<option value="3">Basse</option>';
            $html .= '</select>';
            $html .= '<button id="add-task-btn" class="btn btn-primary">Ajouter</button>';
            $html .= '</div>';
        }

        return $html;
    }

    /**
     * Renders a single task item.
     *
     * @param ToDoItem $task Task data.
     * @return string HTML for the task item.
     */
    private function renderTaskItem(ToDoItem $task): string
    {
        $checked = $task->isChecked() ? 'checked' : '';
        $disabled = $this->user->isProfessor() ? 'disabled' : '';
        $isStudent = $this->user->isStudent();

        $priority = $task->getPriority();
        $priorityClass = [1 => 'priority-high', 2 => 'priority-medium', 3 => 'priority-low'][$priority]
            ?? 'priority-medium';
        $priorityLabel = [1 => 'Haute', 2 => 'Moyenne', 3 => 'Basse'][$priority] ?? 'Moyenne';

        $html = '<li class="task-item ' . $priorityClass . '" data-id="' . $task->getTodoId() . '" ';
        $html .= 'data-priority="' . $priority . '">';
        $html .= '<div class="task-content">';
        $html .= '<label>';
        $html .= '<input type="checkbox" class="task-checkbox" ' . $checked . ' ' . $disabled . '>';
        $html .= '<span class="task-text">' . $task->getTodoDesc() . '</span>';
        $html .= '</label>';
        $html .= '</div>';

        $html .= '<div class="task-actions">';
        if ($isStudent) {
            // Priority selector for students.
            $html .= '<select class="priority-select" ' . ($task->isChecked() ? 'disabled' : '') . '>';
            $html .= '<option value="1" ' . ($priority === 1 ? 'selected' : '') . '>Haute</option>';
            $html .= '<option value="2" ' . ($priority === 2 ? 'selected' : '') . '>Moyenne</option>';
            $html .= '<option value="3" ' . ($priority === 3 ? 'selected' : '') . '>Basse</option>';
            $html .= '</select>';

            $html .= '<button class="btn-delete" title="Supprimer">&times;</button>';
        } else {
            // Static badge for professors.
            $html .= '<span class="badge">' . $priorityLabel . '</span>';
        }
        $html .= '</div>';

        $html .= '</li>';
        return $html;
    }

    /**
     * Returns the title of the To-Do List page.
     *
     * Used in the HTML `<title>` tag and for accessibility.
     *
     * @return string The title of the To-Do List page.
     */
    #[Override]
    protected function getPageTitle(): string
    {
        return 'Page SAE - To Do List - SAE Manager';
    }

    /**
     * Returns the name of the CSS file associated with the To-Do List page.
     *
     * The returned filename will be included in the HTML header for styling purposes.
     *
     * @return string The name of the CSS file.
     */
    #[Override]
    protected function getNameCss(): string
    {
        return 'to-do-list.css';
    }

    /**
     * Returns additional HTML meta headers for the To-Do List page.
     *
     * Includes SEO-related metadata and Open Graph (OG) tags
     * for better social media sharing and indexing.
     *
     * @return string The HTML string containing additional meta headers.
     */
    #[Override]
    protected function getAdditionalHeaders(): string
    {
        $html = '<meta name="description" content="To-Do List SAE Manager">';
        $html .= '<meta name="keywords" content="SAE Manager, SAE, To-Do List">';
        $html .= '<meta name="author" content="Benhafessa-Edelstein-Dargentolle-Griguer-Radjou">';

        $html .= '<meta property="og:title" content="SAE Manager - To-Do List" />';
        $html .= '<meta property="og:url" content="https://www.facebook.com/" />';
        $html .= '<meta property="og:description" content="Gérez vos tâches SAE." />';
        $html .= '<meta property="og:site_name" content="SAE Manager" />';
        $html .= '<meta property="og:type" content="website" />';

        $html .= '<meta property="og:title" content="SAE Manager - To-Do List" />';
        $html .= '<meta property="og:url" content="https://www.linkedin.com/" />';
        $html .= '<meta property="og:description" content="Gérez vos tâches SAE." />';
        $html .= '<meta property="og:site_name" content="SAE Manager" />';
        $html .= '<meta property="og:type" content="website" />';

        $html .= '<meta property="og:title" content="SAE Manager - To-Do List" />';
        $html .= '<meta property="og:url" content="https://www.instagram.com/" />';
        $html .= '<meta property="og:description" content="Gérez vos tâches SAE." />';
        $html .= '<meta property="og:site_name" content="SAE Manager" />';
        $html .= '<meta property="og:type" content="website" />';

        $html .= '<meta name="csrf-token" content="' . SessionService::generateCsrfToken() . '">';

        return $html;
    }

    /**
     * Returns additional JavaScript scripts for the To-Do List page.
     *
     * This script handles the interactive behavior of the To-Do List,
     * including user actions and task management.
     *
     * @return string The HTML <script> tag to include the JavaScript file.
     */
    #[Override]
    protected function getAdditionalScripts(): string
    {
        return '<script src="/scripts/to-do-list.js" defer></script>';
    }
}
