<?php

namespace Views\SAE;

use Core\Views\AbstractView;
use Override;

/**
 * View for the SAE creation page.
 *
 * @category   Views
 * @package    Src
 * @subpackage Views/SAE
 * @author     Dinesh Radjou <dinesh.radjou@univ-amu.fr>
 * @license    https://opensource.org/licenses/MIT MIT License
 * @link       https://github.com/RADJOU-Dinesh-24003262/SAEManager/blob/main/App/src/Views/SAE/CreateSaeView.php
 */
class CreateSaeView extends AbstractView
{
    /**
     * Path to the HTML template file.
     *
     * @var string
     */
    private const TEMPLATE_HTML = __DIR__ . '/create-sae.html';

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
     * Returns an array of keys used in the template for dynamic content replacement.
     *
     * @return array<string, mixed>
     */
    #[Override]
    protected function templateKeys(): array
    {
        $clientsHtml = '<option value="">-- Choisir un client --</option>';
        if (isset($this->data['clients']) && is_array($this->data['clients'])) {
            foreach ($this->data['clients'] as $client) {
                $name = $client['last_name'] . ' '
                        . $client['first_name'] . ' (' . $client['organisation'] . ')';
                $id = $client['user_id'];
                $clientsHtml .= "<option value=\"$id\">$name</option>";
            }
        }

        $errorsHtml = $this->renderErrorMessages($this->getErrors());
        $successHtml = $this->renderSuccessMessage();

        return [
            'clients_options' => $clientsHtml,
            'error_messages' => $errorsHtml,
            'success_message' => $successHtml
        ];
    }

    /**
     * Returns the page title.
     *
     * @return string
     */
    #[Override]
    protected function getPageTitle(): string
    {
        return 'Création d\'une SAE - SAE Manager';
    }

    /**
     * Returns the CSS file name.
     *
     * @return string
     */
    #[Override]
    protected function getNameCss(): string
    {
        return 'create-sae.css';
    }
}
