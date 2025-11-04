<?php

namespace Views\Profile;

use Views\Dashboard\DashboardView;
use Core\AbstractView;

/**
 * Class ProfileView
 * This class represents the view for the profile page of the application.
 * It extends the AbstractView class and provides specific implementations
 * for rendering the profile page.
 *
 * @category   View
 * @package    Src
 * @subpackage Views\Profile
 * @author     Alexandre Benhafessa <alexandre.benhafessa@etu.univ-amu.fr>
 * @author     François Dargentolle <francois.dargentolle@etu.univ-amu.fr>
 * @author     William Edelstein <william.edelstein@etu.univ-amu.fr>
 * @author     Nathan Griguer <nathan.griguer@etu.univ-amu.fr>
 * @author     Dinesh Radjou <dinesh.radjou@etu.univ-amu.fr>
 * @license    MIT License https://opensource.org/licenses/MIT
 * @link       https://github.com/RADJOU-Dinesh-24003262/SAEManager
 */
class ProfileView extends AbstractView
{
    /**
     * Path to the HTML template file used for rendering the profile page.
     *
     * @var string
     */
    private const TEMPLATE_HTML = __DIR__ . '/profile.html';

    /**
     * Returns the path to the HTML template file.
     *
     * @return string The full path to the template file.
     */
    protected function templatePath(): string
    {
        return self::TEMPLATE_HTML;
    }


    /**
     * Returns the list of keys and rendered values used in the HTML template.
     *
     * @return array The list of template keys and values.
     */
    protected function templateKeys(): array
    {
        $user = $this->data['user'];

        return [
            'FULLNAME' => $user->getFullName(),
        ];
    }

    /**
     * Returns the name of the CSS file associated with this view.
     *
     * @return string The CSS filename.
     */
    protected function getNameCss(): string
    {
        return 'profile.css';
    }
}
