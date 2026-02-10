<?php

namespace App\GUI\Controllers\Settings;

use App\GUI\Controllers\BaseController;
use Override;
use App\GUI\Views\Settings\SettingsView;

/**
 * Settings Controller
 *
 * Handle User Settings (GET request)
 *
 * @category Controller
 *
 * @package Src
 *
 * @subpackage Controllers/Settings
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
class SettingsController extends BaseController
{
    /**
     *  Main Controller logic for SettingsController.
     *
     * @return void
     */
    #[Override]
    public function control(): void
    {
        $this->ensureAuthenticated();

        $data['user'] = $this->user;
        $view = new SettingsView($data);
        $view->render();
    }

    /**
     * Check if the controller should handle the current request
     *
     * @param  string $path   The request path.
     * @param  string $method The HTTP request method.
     * @return boolean True if path is /profile and the method is GET.
     */
    #[Override]
    public static function support(string $path, string $method): bool
    {
        return $path === '/settings' and $method === 'GET';
    }
}