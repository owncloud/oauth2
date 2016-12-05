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

namespace OCA\OAuth2\Tests\Unit\Controller;

use OCA\OAuth2\AppInfo\Application;
use OCA\OAuth2\Controller\PageController;
use OCA\OAuth2\Db\AuthorizationCodeMapper;
use OCA\OAuth2\Db\Client;
use OCA\OAuth2\Db\ClientMapper;
use OCP\AppFramework\Http\RedirectResponse;
use PHPUnit_Framework_TestCase;

use OCP\AppFramework\Http\TemplateResponse;


class PageControllerTest extends PHPUnit_Framework_TestCase {

	/** @var PageController $controller */
	private $controller;

	/** @var string $userId */
	private $userId = 'john';

	public function setUp() {
		$request = $this->getMockBuilder('OCP\IRequest')->getMock();

		$app = new Application();
		$container = $app->getContainer();

		/** @var ClientMapper $clientMapper */
		$clientMapper = $container->query('ClientMapper');

		$client = new Client();
		$client->setId('testId7890');
		$client->setSecret('topSecret123');
		$client->setRedirectUri('https://www.example.org');
		$client->setName('Example');
		$clientMapper->insert($client);

		/** @var AuthorizationCodeMapper $authorizationCodeMapper */
		$authorizationCodeMapper = $container->query('AuthorizationCodeMapper');

		$this->controller = new PageController('oauth2', $request, $clientMapper, $authorizationCodeMapper, $this->userId);
	}

	public function testAuthorize() {
		$result = $this->controller->authorize('code', 'client_id', 'redirect_uri', 'state');
		$this->assertTrue($result instanceof RedirectResponse);

		$result = $this->controller->authorize('code', 'testId7890', urldecode('https://www.example.org'), 'state');
		$this->assertTrue($result instanceof TemplateResponse);
	}

}
