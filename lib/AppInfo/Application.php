<?php
namespace OCA\OAuth2\AppInfo;

use \OCP\AppFramework\App;
use \OCA\OAuth2\Db\ClientMapper;

class Application extends App {

    public function __construct(array $urlParams=array()){
        parent::__construct('oauth2', $urlParams);

        $container = $this->getContainer();

        $container->query('OCP\INavigationManager')->add(function () use ($container) {
            $urlGenerator = $container->query('OCP\IURLGenerator');
            $l10n = $container->query('OCP\IL10N');
            return [
                // the string under which your app will be referenced in owncloud
                'id' => 'oauth2',

                // sorting weight for the navigation. The higher the number, the higher
                // will it be listed in the navigation
                'order' => 10,

                // the route that will be shown on startup
                'href' => $urlGenerator->linkToRoute('oauth2.page.index'),

                // the icon that will be shown in the navigation
                // this file needs to exist in img/
                'icon' => $urlGenerator->imagePath('oauth2', 'app.svg'),

                // the title of your application. This will be used in the
                // navigation or on the settings page of your app
                'name' => $l10n->t('OAuth 2.0'),
            ];
        });

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