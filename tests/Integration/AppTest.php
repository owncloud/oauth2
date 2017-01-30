<?php
/**
 * @author Lukas Biermann
 * @author Nina Herrmann
 * @author Wladislaw Iwanzow
 * @author Dennis Meis
 * @author Jonathan Neugebauer
 *
 * @copyright Copyright (c) 2016, Project Seminar "PSSL16" at the University of Muenster.
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

namespace OCA\OAuth2\Tests\Integration\Controller;

use OCA\OAuth2\AppInfo\Application;
use OCP\App\IAppManager;
use OCP\AppFramework\IAppContainer;
use Test\TestCase;

class AppTest extends TestCase {

	/** @var IAppContainer $container */
    private $container;

    public function setUp() {
        parent::setUp();

        $app = new Application();
        $this->container = $app->getContainer();
    }

    public function testAppInstalled() {
		/** @var IAppManager $appManager */
        $appManager = $this->container->query('OCP\App\IAppManager');
        $this->assertTrue($appManager->isInstalled('oauth2'));
    }

}
