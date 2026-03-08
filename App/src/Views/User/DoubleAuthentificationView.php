<?php

namespace Views\User;

use Core\Views\AbstractView;
use Override;

/**
 * View displayed after a successful registration form submission.
 * Asks the user to check their email inbox to confirm their account.
 *
 * @category   View
 * @package    Src
 * @subpackage Views/User
 * @author     Alexandre Benhafessa <alexandre.benhafessa@etu.univ-amu.fr>
 * @author     François Dargentolle <francois.dargentolle@etu.univ-amu.fr>
 * @author     William Edelstein <william.edelstein@etu.univ-amu.fr>
 * @author     Nathan Griguer <nathan.griguer@etu.univ-amu.fr>
 * @author     Dinesh Radjou <dinesh.radjou@etu.univ-amu.fr>
 * @license    MIT License https://opensource.org/licenses/MIT
 * @link       https://github.com/RADJOU-Dinesh-24003262/SAEManager
 */
class DoubleAuthentificationView extends AbstractView
{
    /**
     * Path to the HTML template file.
     *
     * @var string
     */
    private const TEMPLATE_HTML = __DIR__ . '/double-authentification.html';

    /**
     * Name of the CSS file for this view.
     *
     * @var string
     */
    private const CSS_REGISTER_PENDING = '';

    /**
     * The email address the confirmation was sent to.
     *
     * @var string
     */
    private string $email;

    /**
     * Constructor.
     *
     * @param string $email The email address the confirmation link was sent to.
     */
    public function __construct(string $email)
    {
        $this->email = $email;
        parent::__construct();
    }

    /**
     * Returns the path to the HTML template.
     *
     * @return string
     */
    #[Override]
    protected function templatePath(): string
    {
        return self::TEMPLATE_HTML;
    }

    /**
     * Returns keys and values to inject in the template.
     *
     * @return array<string, string>
     */
    #[Override]
    protected function templateKeys(): array
    {
        return [
            'USER_EMAIL' => htmlspecialchars($this->email),
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
        return 'Vérifiez votre boîte mail - SAE Manager';
    }

    /**
     * Returns the CSS filename.
     *
     * @return string
     */
    #[Override]
    protected function getNameCss(): string
    {
        return self::CSS_REGISTER_PENDING;
    }

    /**
     * Returns additional HTML headers for the page.
     *
     * @return string
     */
    #[Override]
    protected function getAdditionalHeaders(): string
    {
        return '<meta name="description" content="Confirmation d\'inscription SAE Manager">
                <meta name="author" content="Benhafessa-Edelstein-Dargentolle-Griguer-Radjou">
                <meta property="og:title" content="SAE Manager" />
                <meta property="og:site_name" content="SAE Manager" />
                <meta property="og:type" content="website" />';
    }
}