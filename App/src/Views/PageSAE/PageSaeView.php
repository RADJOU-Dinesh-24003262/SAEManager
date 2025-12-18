<?php

namespace Views\PageSAE;

use Core\AbstractView;
use Core\Utilis\SessionService;
use Models\SAE\SAERepository;

/**
 * Class PageSaeView
 * This class represents the view for the page of the application where we will see the SAE .
 * It extends the AbstractView class and provides specific implementations for rendering the SAE page.

 * @category View

 * @package Src

 * @subpackage Views/PageSAE

 * @author Alexandre Benhafessa <alexandre.benhafessa@etu.univ-amu.fr>
 * @author François Dargentolle <francois.dargentolle@etu.univ-amu.fr>
 * @author William Edelstein <william.edelstein@etu.univ-amu.fr>
 * @author Nathan Griguer <nathan.griguer@etu.univ-amu.fr>
 * @author Dinesh Radjou <dinesh.radjou@etu.univ-amu.fr>

 * @license MIT License https://opensource.org/licenses/MIT

 * @link https://github.com/RADJOU-Dinesh-24003262/SAEManager
 */
class PageSaeView extends AbstractView
{
    /**
     * The path of the HTML code to display for this view.
     *
     * @var string
     */
    private const TEMPLATE_HTML = __DIR__ . '/pageSae.html';


    /**
     * Returns the path to the HTML template file.
     *
     * @return string
     */
    protected function templatePath(): string
    {
        return self::TEMPLATE_HTML;
    }

    /**
     * Returns an empty array. Implemented from the parent class.
     *
     * This method returns an empty array.
     *
     * @return array An empty array
     */
    protected function templateKeys(): array
    {
        return [
            'SAE_NUM' => $this->data['sae']->getSaeSubjectId(),
            'SAE_NAME' => $this->data['sae']->getSubjectName(),
            'SAE_CONTENT' => $this->getDescriptionSae()
        ];
    }

    /**
     * Generates the description of the SAE.
     *
     * This method constructs an HTML description of the SAE using its attributes.
     *
     * @return string The HTML description of the SAE.
     */
    protected function getDescriptionSae(): string
    {
        $saeRepository = SAERepository::getInstance();
        $content = '<p>Nom de la SAE : ' . $this->data['sae']->getSubjectName() . '</p>';
        $content .= '<p>Début de la SAE : ' . $this->data['sae']->getBeginDate() . '</p>';
        $content .= '<p>Fin de la SAE : ' . $this->data['sae']->getEndDate() . '</p>';

        $profRes = $saeRepository->getResponsibleProfessor($this->data['sae']->getSaeSubjectId());
        $prof = $saeRepository->getResponsibleProfessor($this->data['sae']->getSaeSubjectId());
        $client = $saeRepository->getClientInfo($this->data['sae']->getSaeSubjectId());

        $profResLastName = isset($profRes['last_name']) ? $profRes['last_name'] : 'Inconnu';
        $profResFirstName = isset($profRes['first_name']) ? $profRes['first_name'] : 'Inconnu';

        $profLastName = isset($prof['last_name']) ? $prof['last_name'] : 'Inconnu';
        $profFirstName = isset($prof['first_name']) ? $prof['first_name'] : 'Inconnu';

        $clientLastName = isset($client['last_name']) ? $client['last_name'] : 'Inconnu';
        $clientFirstName = isset($client['first_name']) ? $client['first_name'] : 'Inconnu';

        $content .= '<p> Le Responsable de la ressource est ' . $profResLastName . ' ' . $profResFirstName . '.</p>';
        $content .= '<p> Votre professeur associé à la ressource est ' . $profLastName . ' ' . $profFirstName . '.</p>';
        $content .= '<p> Votre client associé à cette SAE est ' . $clientLastName . ' ' . $clientFirstName . '.</p>';

        $filePath = $this->data['sae']->getFilePath();
        if (file_exists($filePath)) {
            $content .= file_get_contents($filePath);
        }
        return $content;
    }

    /**
     * Returns the name of the page 'Page SAE - SAE Manager'
     * or be used in some cases like displaying it by some isolated texts.

     * @return string the name of the project 'Page SAE - SAE Manager'.
     */
    protected function getPageTitle(): string
    {
        return 'SAE ' . $this->data['sae']->getSubjectName() . ' - SAE Manager';
    }

    /**
     * Returns the name of the CSS file associated with the view.
     *
     * This method should be implemented by subclasses to specify the CSS file
     * that should be included in the HTML header for styling the page.
     *
     * @return string The name of the CSS file.
     */
    protected function getNameCss(): string
    {
        return 'page-sae.css';
    }
    /**
     * Returns additional HTML headers for the Sae page.
     *
     * @return string The additional HTML headers.
     */
    protected function getAdditionalHeaders(): string
    {
        return '<meta name="description" content="Page SAE de SAE Manager">
                <meta name="keywords" content="SAE Manager, SAE">
                <meta name="author" content="Benhafessa-Edelstein-Dargentolle-Griguer-Radjou">
                
                <meta property="og:title" content="Notre site" />
                <meta property="og:url" content="http://www.facebook.com/" />
                <meta property="og:description" content="Pour en savoir plus sur nous" />
                <meta property="og:site_name" content="SAE Manager" />
                <meta property="og:type" content="website" />
                
                <meta property="og:title" content="Notre site" />
                <meta property="og:url" content="http://www.linkedin.com/" />
                <meta property="og:description" content="Pour en savoir plus sur nous" />
                <meta property="og:site_name" content="SAE Manager" />
                <meta property="og:type" content="website" />
                
                <meta property="og:title" content="Notre site" />
                <meta property="og:url" content="http://www.instagram.com/" />
                <meta property="og:description" content="Pour en savoir plus sur nous" />
                <meta property="og:site_name" content="SAE Manager" />
                <meta property="og:type" content="website" />' ;
    }
}
