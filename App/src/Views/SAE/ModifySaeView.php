<?php

namespace Views\SAE;

use Views\BaseSaeView;
use Override;

class ModifySaeView extends BaseSaeView
{
    private const TEMPLATE_HTML = __DIR__ . '/modify-sae.html';

    #[Override]
    protected function templatePath(): string
    {
        return self::TEMPLATE_HTML;
    }

    #[Override]
    protected function templateKeys(): array
    {
        $subject = $this->data['sae']['subject'];

        // 🔑 POINT IMPORTANT 6 : Pré-remplir les champs du formulaire
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

    private function getDescription(): string
    {
        $filePath = $this->data['sae']['subject']->getFilePath();

        if ($filePath) {
            $fullPath = __DIR__ . '/../../../../storage/sae_descriptions/' . $filePath;
            if (file_exists($fullPath)) {
                return file_get_contents($fullPath);
            }
        }

        return '';
    }

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

    #[Override]
    protected function getPageTitle(): string
    {
        return 'Modifier SAE - SAE Manager';
    }

    #[Override]
    protected function getNameCss(): string
    {
        return 'create-sae.css';
    }
}

?>