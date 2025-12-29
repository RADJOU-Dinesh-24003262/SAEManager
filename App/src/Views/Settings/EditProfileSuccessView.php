<?php

namespace Views\Settings;

use Core\Views\AbstractView;
use Override;

/**
 * Edit Profile Success View
 *
 * Display the success page after profile update
 *
 * @category View
 *
 * @package Src
 *
 * @subpackage Views/Settings
 *
 * @author Alexandre Benhafessa <alexandre.benhafessa@etu.univ-amu.fr>
 * @author François Dargentolle <francois.dargentolle@etu.univ-amu.fr>
 * @author William Edelstein <william.edelstein@etu.univ-amu.fr>
 * @author Nathan Griguer <nathan.griguer@etu.univ-amu.fr>
 * @author Dinesh Radjou <dinesh.radjou@etu.univ-amu.fr>
 *
 * @license MIT License https://opensource.org/licenses/MIT
 *
 * @link https://github.com/RADJOU-Dinesh-24003262/SAEManager
 */
class EditProfileSuccessView extends AbstractView
{
    private const TEMPLATE_HTML = __DIR__ . '/edit-profile-success.html';

    /**
     * Get the path to the HTML template file.
     *
     * @return string The path to the template.
     */
    #[Override]
    protected function templatePath(): string
    {
        return self::TEMPLATE_HTML;
    }

    /**
     * Get the template keys for data replacement.
     *
     * @return array<string, mixed> Associative array of template keys and their values.
     */
    #[Override]
    protected function templateKeys(): array
    {

        return [];
    }

    /**
     * Get the CSS file name for this view.
     *
     * @return string The CSS file name.
     */
    #[Override]
    protected function getNameCss(): string
    {
        return 'profile.css';
    }
}
