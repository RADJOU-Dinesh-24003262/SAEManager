<?php

namespace Views\ToDoList;

use Views\AbstractView;
use Utilis\SessionService;

/**
 * Class ToDoListView
 *
 * This class represents the view for the page of the application where we will see the to-do list of the students.
 * It extends the AbstractView class and provides specific implementations
 * for rendering the SAE page.
 */
class ToDoListView extends AbstractView
{
    private const TEMPLATE_HTML = __DIR__ . '/to-do-list.html';


    protected function templatePath(): string
    {
        return self::TEMPLATE_HTML;
    }

    protected function templateKeys(): array
    {
        return [];
    }

    protected function getPageTitle(): string
    {
        return 'Page SAE - To Do List - SAEManager';
    }

    protected function getNameCss(): string
    {
        return 'to-do-list.css';
    }
    /**
     * Returns additional HTML headers for the To-Do List page.
     *
     * @return string The additional HTML headers.
     */
    protected function getAdditionalHeaders(): string
    {
        return '<meta name="description" content="Page SAE de SAEManager partie To Do List">
                <meta name="keywords" content="SAEManager, SAE, to do list">
                <meta name="author" content="Benhafessa-Edelstein-Dargentolle-Griguer-Radjou">
                
                <meta property="og:title" content="Notre site" />
                <meta property="og:url" content="http://www.facebook.com/" />
                <meta property="og:description" content="Pour en savoir plus sur nous" />
                <meta property="og:site_name" content="SAEManager" />
                <meta property="og:type" content="website" />
                
                <meta property="og:title" content="Notre site" />
                <meta property="og:url" content="http://www.linkedin.com/" />
                <meta property="og:description" content="Pour en savoir plus sur nous" />
                <meta property="og:site_name" content="SAEManager" />
                <meta property="og:type" content="website" />
                
                <meta property="og:title" content="Notre site" />
                <meta property="og:url" content="http://www.instagram.com/" />
                <meta property="og:description" content="Pour en savoir plus sur nous" />
                <meta property="og:site_name" content="SAEManager" />
                <meta property="og:type" content="website" />' ;
    }

    protected function getAdditionalScripts(): string
    {
        return '<script src="scripts/to-do-list.js" defer></script>';
    }
}
