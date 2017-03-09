<?php
/**
 * @author Lukas Biermann
 * @author Nina Herrmann
 * @author Wladislaw Iwanzow
 * @author Dennis Meis
 * @author Jonathan Neugebauer
 *
 * @copyright Copyright (c) 2017, Project Seminar "PSSL16" at the University of Muenster.
 * @license AGPL-3.0
 *
 * This code is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License, version 3,
 * as published by the Free Software Foundation.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU Affero General Public License for more details.
 *
 * You should have received a copy of the GNU Affero General Public License, version 3,
 * along with this program.  If not, see <http://www.gnu.org/licenses/>
 */

namespace OCA\OAuth2\AppInfo;

use OCA\OAuth2\Hooks\UserHooks;
use OCA\OAuth2\Sabre\OAuth2;
use OCP\AppFramework\App;
use OCP\SabrePluginEvent;
use Sabre\DAV\Auth\Plugin;

class Application extends App {

	/**
	 * Application constructor.
	 *
	 * @param array $urlParams Variables extracted from the routes.
	 */
	public function __construct(array $urlParams = array()) {
		parent::__construct('oauth2', $urlParams);

		$container = $this->getContainer();

		// Logger
		$container->registerService('Logger', function ($c) {
			return $c->query('ServerContainer')->getLogger();
		});

		// User Manager
		$container->registerService('UserManager', function($c) {
			return $c->query('ServerContainer')->getUserManager();
		});

		// Hooks
		$container->registerService('UserHooks', function ($c) {
			return new UserHooks(
				$c->query('ServerContainer')->getUserManager(),
				$c->query('OCA\OAuth2\Db\AuthorizationCodeMapper'),
				$c->query('OCA\OAuth2\Db\AccessTokenMapper'),
				$c->query('OCA\OAuth2\Db\RefreshTokenMapper'),
				$c->query('Logger'),
				$c->query('AppName')
			);
		});

		// Add event listener
		$dispatcher = $this->getContainer()->getServer()->getEventDispatcher();
		$dispatcher->addListener('OCA\DAV\Connector\Sabre::authInit', function ($event) use ($container) {
			if ($event instanceof SabrePluginEvent) {
				$authPlugin = $event->getServer()->getPlugin('auth');
				if ($authPlugin instanceof Plugin) {
					$authPlugin->addBackend(
						new OAuth2(\OC::$server->getSession(),
							\OC::$server->getUserSession(),
							\OC::$server->getRequest(),
							'principals/')
					);
				}
			}
		});
	}

	/**
	 * Registers settings pages.
	 */
	public function registerSettings() {
		\OCP\App::registerAdmin('oauth2', 'settings-admin');
		\OCP\App::registerPersonal('oauth2', 'settings-personal');
	}

}
