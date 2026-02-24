<?php

namespace Views\Settings;

use Core\Views\AbstractView;
use Models\Entity\User\User;
use Override;

/**
 * Class SettingsView
 * This class represents the view for the profile page of the application.
 * It extends the AbstractView class and provides specific implementations
 * for rendering the profile page.
 *
 * @category   View
 * @package    Src
 * @subpackage Views/Settings
 * @author     Alexandre Benhafessa <alexandre.benhafessa@etu.univ-amu.fr>
 * @author     François Dargentolle <francois.dargentolle@etu.univ-amu.fr>
 * @author     William Edelstein <william.edelstein@etu.univ-amu.fr>
 * @author     Nathan Griguer <nathan.griguer@etu.univ-amu.fr>
 * @author     Dinesh Radjou <dinesh.radjou@etu.univ-amu.fr>
 * @license    MIT License https://opensource.org/licenses/MIT
 * @link       https://github.com/RADJOU-Dinesh-24003262/SAEManager
 */
class SettingsView extends AbstractView
{
    /**
     * Path to the HTML template file used for rendering the profile page.
     *
     * @var string
     */
    private const TEMPLATE_HTML = __DIR__ . '/settings.html';


    /**
     * SettingsView constructor.
     *
     * Initializes the view by retrieving flash messages (errors and success)
     * from the session service and passing them to the parent constructor.
     *
     * @param array<string, mixed> $data Data User from current session.
     * @return void
     */
    public function __construct(array $data)
    {
        parent::__construct($data);
    }

    /**
     * Returns the path to the HTML template file.
     *
     * @return string The full path to the template file.
     */
    #[Override]
    protected function templatePath(): string
    {
        return self::TEMPLATE_HTML;
    }


    /**
     * Returns the list of keys and rendered values used in the HTML template.
     *
     * @return array<string, string> The list of template keys and values.
     */
    #[Override]
    protected function templateKeys(): array
    {
        $user = $this->data['user'];

        return [
            'STATUS' => $user->getRoleLabel(),
            'FIRSTNAME' => $user->getFirstName(),
            'LASTNAME' => $user->getLastName(),
            'EMAIL' => $user->getEmail(),
            'PHONE' => $user->getPhone(),
        ];
    }



    /**
     * Returns the name of the CSS file associated with this view.
     *
     * @return string The CSS filename.
     */
    #[Override]
    protected function getNameCss(): string
    {
        return 'profile.css';
    }
}
