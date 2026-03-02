<?php

namespace Views\SAE;

use Core\Views\AbstractView;
use Override;
use Models\Entity\User\Client;

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
     * The clients data.
     *
     * @var array<Client>
     */
    private array $clients;


    /**
     * Constructor.
     *
     * @param array<string, mixed> $data The data to initialize the view with.
     */
    public function __construct(array $data = [])
    {
        $this->clients = $data['clients'];
        parent::__construct($data);
    }

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
        if (isset($this->clients)) {
            foreach ($this->clients as $client) {
                $name = $client->getLastName() . ' '
                        . $client->getFirstName() . ' (' . $client->getOrganisation() . ')';
                $clientId = $client->getUserId();
                $clientsHtml .= "<option value=\"$clientId\">$name</option>";
            }
        }

        $errorsHtml = $this->renderErrorMessages($this->data['errors'] ?? []);
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
