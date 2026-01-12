<?php

namespace Views\SAE;

use Views\BaseSaeView;
use Override;
use Models\SAE\SAEGroup;

/**
 * Class ManageGroupsView
 *
 * View for managing SAE groups.
 *
 * @category   Views
 * @package    Src
 * @subpackage Views/SAE
 * @author     Dinesh Radjou <dinesh.radjou@etu.univ-amu.fr>
 * @license    MIT License https://opensource.org/licenses/MIT
 * @link       https://github.com/RADJOU-Dinesh-24003262/SAEManager
 */
class ManageGroupsView extends BaseSaeView
{
    /**
     * Path to the HTML template file.
     *
     * @var string
     */
    private const TEMPLATE_HTML = __DIR__ . '/manage-groups.html';

    /**
     * Returns the path to the HTML template file.
     *
     * @return string
     */
    #[Override]
    protected function templatePath(): string
    {
        return self::TEMPLATE_HTML;
    }

    /**
     * Returns the CSS file name.
     *
     * @return string
     */
    #[Override]
    protected function getNameCss(): string
    {
        return 'manage-groups.css';
    }

    /**
     * Returns the page title.
     *
     * @return string
     */
    #[Override]
    protected function getPageTitle(): string
    {
        $subjectName = $this->getSubject()->getSubjectName();
        return 'Gestion des Groupes - ' . $subjectName;
    }

    /**
     * Returns an array of keys used in the template for dynamic content replacement.
     *
     * @return array<string, mixed>
     */
    #[Override]
    protected function templateKeys(): array
    {
        return array_merge(
            $this->getCommonSaeTemplateKeys(),
            [
                'sae_name' => $this->getSubject()->getSubjectName(),
                'sae_id' => (string) $this->getSubject()->getSaeSubjectId(),
                'messages' => $this->getMessagesHtml(),
                'professors_options' => $this->generateProfessorsOptions(),
                'groups_html' => $this->generateGroupsHtml(),
            ]
        );
    }

    /**
     * Generates HTML for the groups list.
     *
     * @return string
     */
    private function generateGroupsHtml(): string
    {
        /** @var array{groups: array<int, array{group: SAEGroup, students: array<int, array<string, mixed>>}>, all_professors: array<int, array<string, mixed>>} $saeData */
        $saeData = $this->data['sae'];

        $groups = $saeData['groups'];
        $professors = $saeData['all_professors'];

        /** @var array<int, array<string, mixed>> $availableStudents */
        $availableStudents = $this->data['available_students'];

        $saeId = $this->getSubject()->getSaeSubjectId();

        // Organize students by Year and TD.
        $groupedStudents = [];
        foreach ($availableStudents as $student) {
            $yearVal = $student['year'] ?? 'Inconnu';
            if (!is_string($yearVal) && !is_int($yearVal)) {
                $yearVal = 'Inconnu';
            }
            $year = (string)$yearVal;

            $tdVal = $student['td'] ?? 'N/A';
            if (!is_string($tdVal) && !is_int($tdVal)) {
                $tdVal = 'N/A';
            }
            $td = (string)$tdVal;

            $key = "Année " . $year . " - TD " . $td;
            $groupedStudents[$key][] = $student;
        }
        ksort($groupedStudents);

        if (empty($groups)) {
            return '<p>Aucun groupe créé pour cette SAE.</p>';
        }

        $html = '';

        foreach ($groups as $groupData) {
            $group = $groupData['group'];
            $students = $groupData['students'];
            $groupId = $group->getSaeGroupId();
            $groupProfId = $group->getProfessorId();

            $profName = 'Non assigné';
            foreach ($professors as $prof) {
                if ($prof['user_id'] == $groupProfId) {
                    $profName = $prof['first_name'] . ' ' . $prof['last_name'];
                    break;
                }
            }

            $html .= '<article class="group-card">';

            // Header.
            $html .= '<header class="group-header">';
            $html .= '<h4>Groupe #' . $groupId . '</h4>';
            $html .= '<form action="/sae/' . $saeId . '/groups/delete" method="post" ' .
                     'onsubmit="return confirm(\'Voulez-vous vraiment supprimer ce groupe ?\');">';
            $html .= '<input type="hidden" name="group_id" value="' . $groupId . '">';
            $html .= '<input type="hidden" name="sae_id" value="' . $saeId . '">';
            $html .= '<button type="submit" class="btn-danger-sm">Supprimer</button>';
            $html .= '</form>';
            $html .= '</header>';

            // Professor info.
            $html .= '<div class="group-info">';
            $html .= '<strong>Professeur référent :</strong> ' . $profName;
            $html .= '</div>';

            // Students list.
            $html .= '<div class="students-list">';
            $html .= '<h5>Étudiants (' . count($students) . ')</h5>';
            $html .= '<ul class="student-items">';
            foreach ($students as $student) {
                /** @var array<string, string> $student */
                $student = $student;
                $html .= '<li class="student-item">';
                $html .= '<span>' . (string) $student['first_name'] . ' ' . (string) $student['last_name'] . ' (' .
                         ($student['group_name'] ?? $student['td'] ?? '') . ')</span>';
                $html .= '<form action="/sae/' . $saeId . '/groups/remove-student" method="post" ' .
                         'style="display:inline;">';
                $html .= '<input type="hidden" name="group_id" value="' . $groupId . '">';
                $html .= '<input type="hidden" name="student_id" value="' . $student['student_id'] . '">';
                $html .= '<input type="hidden" name="sae_id" value="' . $saeId . '">';
                $html .= '<button type="submit" class="btn-remove">(Retirer)</button>';
                $html .= '</form>';
                $html .= '</li>';
            }
            $html .= '</ul>';
            $html .= '</div>';

            // Add student form.
            $html .= '<div class="add-student-form">';
            $html .= '<form action="/sae/' . $saeId . '/groups/add-student" method="post">';
            $html .= '<input type="hidden" name="group_id" value="' . $groupId . '">';
            $html .= '<input type="hidden" name="sae_id" value="' . $saeId . '">';

            $html .= '<div class="add-student-wrapper">';
            $html .= '<select name="student_id" required class="student-select">';
            $html .= '<option value="">Ajouter un étudiant...</option>';

            foreach ($groupedStudents as $groupLabel => $studentsInGroup) {
                $html .= '<optgroup label="' . htmlspecialchars($groupLabel) . '">';
                foreach ($studentsInGroup as $student) {
                    $label = $student['last_name'] . ' ' . $student['first_name'];
                    if (!empty($student['major'])) {
                        $label .= ' (' . $student['major'] . ')';
                    }
                    $html .= '<option value="' . $student['student_id'] . '">' . htmlspecialchars($label) . '</option>';
                }
                $html .= '</optgroup>';
            }

            $html .= '</select>';
            $html .= '<button type="submit" class="btn-add">OK</button>';
            $html .= '</div>';

            $html .= '</form>';
            $html .= '</div>';

            $html .= '</article>';
        }

        return $html;
    }

    /**
     * Generates options for the professor select box.
     *
     * @return string
     */
    private function generateProfessorsOptions(): string
    {
        /** @var array<int, array<string, mixed>> $professors */
        $professors = $this->data['all_professors'];
        $html = '';
        foreach ($professors as $prof) {
            $html .= '<option value="' . $prof['user_id'] . '">' . $prof['last_name'] . ' ' .
                     $prof['first_name'] . '</option>';
        }
        return $html;
    }

    /**
     * Generates HTML for success/error messages.
     *
     * @return string
     */
    private function getMessagesHtml(): string
    {
        $html = '';

        $success = $this->getSuccess();
        if (!empty($success)) {
            $html .= '<div class="alert alert-success">' . $success . '</div>';
        }

        $errors = $this->getErrors();
        if (!empty($errors)) {
            $html .= $this->renderErrorMessages($errors);
        }

        return $html;
    }
}
