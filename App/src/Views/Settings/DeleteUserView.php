<?php

namespace Views\Settings;

use Core\AbstractView;

/**
 * Class DeleteUserView
 * This class represents the view for the deleteuser page of the application.
 * It extends the AbstractView class and provides specific implementations
 * for rendering the deleteuser page.
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
class DeleteUserView extends AbstractView
{
    private const TEMPLATE_HTML = __DIR__ . '/delete-user-view.html';

    /**
     * Path to the HTML template file used for rendering the profile page.
     *
     * @return string
     */
    #[\Override]
    public function templatePath(): string
    {
        return self::TEMPLATE_HTML;
    }

    /**
     * Returns the list of keys and rendered values used in the HTML template.
     *
     * @return array<string, string> The list of template keys and values.
     */
    #[\Override]
    public function templateKeys(): array
    {
        $user = $this->data['user'];

        return [
            'EMAIL' => $user->getEmail()
        ];
    }

    /**
     * Returns the name of the CSS file associated with this view.
     *
     * @return string The CSS filename.
     */
    #[\Override]
    public function getNameCss(): string
    {
        return "";
    }
}
