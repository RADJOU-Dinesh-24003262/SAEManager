<?php
namespace Views;

abstract class AbstractView 
{
    protected array $data = [];

    public function __construct(array $data = [])
    {
        $this->data = $data;
    }

    protected function renderBody(): void
    {
        $template = file_get_contents($this->templatePath());
        
        // Remplacement des clés du template
        foreach ($this->templateKeys() as $key => $value) {
            $template = str_replace("{{{$key}}}", $value, $template);
        }
        
        echo $template;
    }

    abstract protected function templatePath(): string;
    
    /**
     * @return array<string, string>
     */
    abstract protected function templateKeys(): array;

    public function render(): void
    {
        $this->renderHeader();
        $this->renderBody();
        $this->renderFooter();
    }

    protected function renderHeader(): void
    {
        echo '<!DOCTYPE html>
        <html lang="fr">
        <head>
            <meta charset="UTF-8">
            <meta http-equiv="X-UA-Compatible" content="IE=edge">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <title>' . $this->getPageTitle() . '</title>
            <link rel="stylesheet" href="styles/'. $this->getNameCss().'">
            <link rel="stylesheet" href="styles/header.css">
            ' . $this->getAdditionalHeaders() . '
        </head>
        <body>
        <header>
                <h1 class="saeManager">SAEManager</h1>
                <img src="Image/logoamu.png" alt="Logo AMU Header" >
                <nav class="navBar">
                    <a href="/" class="nav-link">Accueil</a>
                    <a href="/login" class="nav-link">Connexion</a>
                    <a href="/register" class="nav-link">Inscription</a>
                </nav>
         </header>
        ';
    }
    
    abstract protected function getNameCss(): string;

    protected function renderFooter(): void
    {
        echo $this->getAdditionalScripts() . '
        </body>
        </html>';
    }

    protected function getPageTitle(): string
    {
        return 'SAEManager';
    }

    protected function getAdditionalHeaders(): string
    {
        return '';
    }

    protected function getAdditionalScripts(): string
    {
        return '';
    }
}