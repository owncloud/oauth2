<?php
namespace OCA\OAuth2\AppInfo;

use \OCP\AppFramework\App;

class Application extends App {

    /**
     * Application constructor.
     *
     * @param array $urlParams an array with variables extracted from the routes
     */
    public function __construct(array $urlParams=array()){
        parent::__construct('oauth2', $urlParams);
    }

    /**
     * Registers the settings for the app.
     */
    public function registerSettings() {
        \OCP\App::registerAdmin('oauth2', 'lib/settings-admin');
        \OCP\App::registerPersonal('oauth2', 'lib/settings-personal');
    }

}
