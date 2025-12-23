<?php

namespace Views\ToDoList;

use Core\AbstractView;
use Core\Utilis\SessionService;

/**
 * Class ToDoListView
 *
 * Represents the view for the "To-Do List" page of the SAE Manager application.
 * This view is responsible for displaying the to-do list of the students in a specific SAE.
 * It extends {@see AbstractView} and provides specific implementations
 * for rendering the To-Do List page, including the associated CSS file,
 * template path, and metadata headers.
 *
 * @category   View
 * @package    Src
 * @subpackage Views\ToDoList
 * @author     Alexandre Benhafessa <alexandre.benhafessa@etu.univ-amu.fr>
 * @author     François Dargentolle <francois.dargentolle@etu.univ-amu.fr>
 * @author     William Edelstein <william.edelstein@etu.univ-amu.fr>
 * @author     Nathan Griguer <nathan.griguer@etu.univ-amu.fr>
 * @author     Dinesh Radjou <dinesh.radjou@etu.univ-amu.fr>
 * @license    MIT License https://opensource.org/licenses/MIT
 * @link       https://github.com/RADJOU-Dinesh-24003262/SAEManager
 */
class ToDoListView extends AbstractView
{
    private const TEMPLATE_HTML = __DIR__ . '/to-do-list.html';

    // -------------------------------------------------------------------------
    // Constructor
    // -------------------------------------------------------------------------

    /**
     * Returns the path to the HTML template file.
     *
     * @return string The full path to the HTML template.
     */
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
     * @return array<string, mixed> An empty associative array.
     */
    protected function templateKeys(): array
    {
        $errors = $this->data['errors'];

        return [
            'ERROR_MESSAGES' => $this->renderErrorMessages($errors),
            'SAE_NUM' => $this->data['sae']->getSaeSubjectId(),
            'SAE_NAME' => $this->data['sae']->getSubjectName()
        ];
    }

    /**
     * Returns the title of the To-Do List page.
     *
     * Used in the HTML `<title>` tag and for accessibility.
     *
     * @return string The title of the To-Do List page.
     */
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
    protected function getAdditionalHeaders(): string
    {
        return '<meta name="description" content="Page SAE de SAE Manager partie To-Do List">
                <meta name="keywords" content="SAE Manager, SAE, To-Do List">
                <meta name="author" content="Benhafessa-Edelstein-Dargentolle-Griguer-Radjou">

                <meta property="og:title" content="SAE Manager - To-Do List" />
                <meta property="og:url" content="https://www.facebook.com/" />
                <meta property="og:description" content="Consultez la liste des tâches de votre SAE sur SAE Manager." />
                <meta property="og:site_name" content="SAE Manager" />
                <meta property="og:type" content="website" />

                <meta property="og:title" content="SAE Manager - To-Do List" />
                <meta property="og:url" content="https://www.linkedin.com/" />
                <meta property="og:description" content="Consultez la liste des tâches de votre SAE sur SAE Manager." />
                <meta property="og:site_name" content="SAE Manager" />
                <meta property="og:type" content="website" />

                <meta property="og:title" content="SAE Manager - To-Do List" />
                <meta property="og:url" content="https://www.instagram.com/" />
                <meta property="og:description" content="Consultez la liste des tâches de votre SAE sur SAE Manager." />
                <meta property="og:site_name" content="SAE Manager" />
                <meta property="og:type" content="website" />';
    }

    /**
     * Returns additional JavaScript scripts for the To-Do List page.
     *
     * This script handles the interactive behavior of the To-Do List,
     * including user actions and task management.
     *
     * @return string The HTML <script> tag to include the JavaScript file.
     */
    protected function getAdditionalScripts(): string
    {
        return '<script src="/scripts/to-do-list.js" defer></script>';
    }
}
