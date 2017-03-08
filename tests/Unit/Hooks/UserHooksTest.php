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

namespace OCA\OAuth2\Tests\Unit\Hooks;

use OCA\OAuth2\AppInfo\Application;
use OCA\OAuth2\Hooks\UserHooks;
use PHPUnit_Framework_TestCase;

class UserHooksTest extends PHPUnit_Framework_TestCase {

	/** @var UserHooks $userHooks */
	private $userHooks;

	public function setUp() {
		parent::setUp();

		$app = new Application();
		$container = $app->getContainer();

		$this->userHooks = new UserHooks(
			$container->query('ServerContainer')->getUserManager(),
			$container->query('OCA\OAuth2\Db\AuthorizationCodeMapper'),
			$container->query('OCA\OAuth2\Db\AccessTokenMapper'),
			$container->query('OCA\OAuth2\Db\RefreshTokenMapper'),
			$container->query('OCP\ILogger'),
			$container->query('AppName')
		);
	}

	public function testRegister() {
		// Calling the register() function to check for exceptions.
		$this->userHooks->register();
	}

}
