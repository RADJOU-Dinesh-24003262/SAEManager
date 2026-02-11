<?php

namespace Views\SAE;

use Views\BaseSaeView;
use Override;

/**
 * View for the SAE modification page.
 *
 * Renders the form to modify an existing SAE, pre-filled with current data.
 * Extends BaseSaeView to leverage common SAE view functionality.
 *
 * @category   Views
 * @package    Src
 * @subpackage Views/SAE
 * @author     Alexandre Benhafessa <alexandre.benhafessa@etu.univ-amu.fr>
 * @author     François Dargentolle <francois.dargentolle@etu.univ-amu.fr>
 * @author     William Edelstein <william.edelstein@etu.univ-amu.fr>
 * @author     Nathan Griguer <nathan.griguer@etu.univ-amu.fr>
 * @author     Dinesh Radjou <dinesh.radjou@etu.univ-amu.fr>
 * @license    MIT License https://opensource.org/licenses/MIT
 * @link       https://github.com/RADJOU-Dinesh-24003262/SAEManager
 */
class ModifySaeView extends BaseSaeView
{
    /**
     * Path to the HTML template.
     *
     * @var string
     */
    private const TEMPLATE_HTML = __DIR__ . '/modify-sae.html';

    /**
     * Returns the path to the HTML template.
     *
     * @return string The absolute path to the template file.
     */
    #[Override]
    protected function templatePath(): string
    {
        return self::TEMPLATE_HTML;
    }

    /**
     * Prepares the variables to be passed to the template.
     *
     * Merges common SAE view keys with specific data for the modification form,
     * including pre-filled values, client options, and feedback messages.
     *
     * @return array<string, mixed> The associative array of template keys.
     */
    #[Override]
    protected function templateKeys(): array
    {
        $subject = $this->data['sae']['subject'];

        return array_merge(
            $this->getCommonSaeTemplateKeys(),
            [
                'sae_id' => $subject->getSaeSubjectId(),
                'name_sae' => htmlspecialchars($subject->getSubjectName()),
                'begin_date' => $subject->getBeginDate(),
                'end_date' => $subject->getEndDate(),
                'client_id' => $subject->getClientId() ?? '',
                'description' => $this->getDescription(),
                'clients_options' => $this->generateClientsOptions(),
                'error_messages' => $this->renderErrorMessages($this->data['errors'] ?? []),
                'success_message' => $this->renderSuccessMessage()
            ]
        );
    }

    /**
     * Retrieves the content of the SAE description file.
     *
     * reads the markdown file associated with the SAE if it exists.
     *
     * @return string The content of the description file or empty string if not found.
     */
    private function getDescription(): string
    {
        $filePath = $this->data['sae']['subject']->getFilePath();

        if ($filePath) {
            $fullPath = __DIR__ . '/../../../../storage/sae_descriptions/' . $filePath;
            if (file_exists($fullPath)) {
                $content = file_get_contents($fullPath);
                // Correction ici : on vérifie que le contenu n'est pas false
                return $content !== false ? $content : '';
            }
        }

        return '';
    }

    /**
     * Generates HTML options for the client selection dropdown.
     *
     * Iterates through available clients and marks the current client as selected.
     *
     * @return string The HTML string of <option> tags.
     */
    private function generateClientsOptions(): string
    {
        $clientsHtml = '<option value="">-- Aucun client (optionnel) --</option>';
        $currentClientId = $this->data['sae']['subject']->getClientId();

        if (isset($this->data['clients']) && is_array($this->data['clients'])) {
            foreach ($this->data['clients'] as $client) {
                $name = $client['last_name'] . ' ' . $client['first_name'] .
                    ' (' . $client['organisation'] . ')';
                $id = $client['user_id'];
                $selected = ($id == $currentClientId) ? 'selected' : '';
                $clientsHtml .= "<option value=\"$id\" $selected>$name</option>";
            }
        }

        return $clientsHtml;
    }

    /**
     * Returns the page title.
     *
     * @return string The title of the page.
     */
    #[Override]
    protected function getPageTitle(): string
    {
        return 'Modifier SAE - SAE Manager';
    }

    /**
     * Returns the CSS file name.
     *
     * @return string The name of the CSS file.
     */
    #[Override]
    protected function getNameCss(): string
    {
        return 'create-sae.css';
    }
}
