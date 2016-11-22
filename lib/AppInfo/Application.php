<?php
namespace OCA\OAuth2\AppInfo;

use \OCP\AppFramework\App;
use \OCA\OAuth2\Db\ClientMapper;

class Application extends App {

    public function __construct(array $urlParams=array()){
        parent::__construct('oauth2', $urlParams);

        $container = $this->getContainer();

        $container->registerService('ClientMapper', function($c) {
            return new ClientMapper($c->query('ServerContainer')->getDb());
        });
    }

    /**
     * Registers the settings for the app.
     */
    public function registerSettings() {
        \OCP\App::registerAdmin('oauth2', 'lib/settings-admin');
        \OCP\App::registerPersonal('oauth2', 'lib/settings-personal');
    }

    public function getDatabaseConnection() {
        return $this->getContainer()->getServer()->getDatabaseConnection();
    }

}
