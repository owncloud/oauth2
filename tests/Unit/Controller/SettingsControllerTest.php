<?php
/**
 * @author Project Seminar "sciebo@Learnweb" of the University of Muenster
 * @copyright Copyright (c) 2017, University of Muenster
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
use OCA\OAuth2\Controller\SettingsController;
use OCA\OAuth2\Db\AccessToken;
use OCA\OAuth2\Db\AccessTokenMapper;
use OCA\OAuth2\Db\AuthorizationCode;
use OCA\OAuth2\Db\AuthorizationCodeMapper;
use OCA\OAuth2\Db\Client;
use OCA\OAuth2\Db\ClientMapper;
use OCA\OAuth2\Db\RefreshToken;
use OCA\OAuth2\Db\RefreshTokenMapper;
use OCP\AppFramework\Http\RedirectResponse;
use OCP\IRequest;
use OCP\IURLGenerator;
use PHPUnit\Framework\TestCase;

class SettingsControllerTest extends TestCase {

	/** @var string $name */
	private $appName;

	/** @var SettingsController $controller */
	private $controller;

	/** @var ClientMapper $clientMapper */
	private $clientMapper;

	/** @var AuthorizationCodeMapper $authorizationCodeMapper */
	private $authorizationCodeMapper;

	/** @var AccessTokenMapper */
	private $accessTokenMapper;

	/** @var RefreshTokenMapper */
	private $refreshTokenMapper;

	/** @var IURLGenerator | \PHPUnit\Framework\MockObject\MockObject */
	private $urlGenerator;

	/** @var string $userId */
	private $userId = 'john';

	/** @var Client $client */
	private $client;

	/** @var string $redirectUri */
	private $redirectUri = 'https://owncloud.org';

	/** @var string $name */
	private $name = 'ownCloud';

	public function setUp() {
		parent::setUp();

		$app = new Application();
		$container = $app->getContainer();

		$this->appName = $container->query('AppName');

		$this->clientMapper = $container->query('OCA\OAuth2\Db\ClientMapper');
		$this->clientMapper->deleteAll();
		$this->authorizationCodeMapper = $container->query('OCA\OAuth2\Db\AuthorizationCodeMapper');
		$this->authorizationCodeMapper->deleteAll();
		$this->accessTokenMapper = $container->query('OCA\OAuth2\Db\AccessTokenMapper');
		$this->accessTokenMapper->deleteAll();
		$this->refreshTokenMapper = $container->query('OCA\OAuth2\Db\RefreshTokenMapper');
		$this->refreshTokenMapper->deleteAll();

		/** @var Client $client */
		$client = new Client();
		$client->setIdentifier('NXCy3M3a6FM9pecVyUZuGF62AJVJaCfmkYz7us4yr4QZqVzMIkVZUf1v2IzvsFZa');
		$client->setSecret('9yUZuGF6pecVaCfmIzvsFZakYNXCyr4QZqVzMIky3M3a6FMz7us4VZUf2AJVJ1v2');
		$client->setRedirectUri($this->redirectUri);
		$client->setName($this->name);
		$client->setAllowSubdomains(false);
		$this->client = $this->clientMapper->insert($client);

		$authorizationCode = new AuthorizationCode();
		$authorizationCode->setCode('kYz7us4yr4QZyUZuMIkVZUf1v2IzvsFZaNXCy3M3amqVGF62AJVJaCfz6FM9pecV');
		$authorizationCode->setClientId($this->client->getId());
		$authorizationCode->setUserId($this->userId);
		$authorizationCode->resetExpires();
		$this->authorizationCodeMapper->insert($authorizationCode);

		$accessToken = new AccessToken();
		$accessToken->setToken('3M3amqVGF62kYz7us4yr4QZyUZuMIAZUf1v2IzvsFJVJaCfz6FM9pecVkVZaNXCy');
		$accessToken->setClientId($this->client->getId());
		$accessToken->setUserId($this->userId);
		$accessToken->resetExpires();
		$this->accessTokenMapper->insert($accessToken);

		$refreshToken = new RefreshToken();
		$refreshToken->setToken('3M3amqVGF62kYz7us4yr4QZyUZuMIAZUf1v2IzvsFJVJaCfz6FM9pecVkVZaNXCy');
		$refreshToken->setClientId($this->client->getId());
		$refreshToken->setUserId($this->userId);
		$refreshToken->setAccessTokenId($accessToken->getId());
		$this->refreshTokenMapper->insert($refreshToken);

		$this->urlGenerator = $this->getMockBuilder(IURLGenerator::class)->getMock();

		$this->controller = new SettingsController(
			$this->appName,
			$this->getMockBuilder(IRequest::class)->getMock(),
			$this->clientMapper,
			$this->authorizationCodeMapper,
			$this->accessTokenMapper,
			$this->refreshTokenMapper,
			$this->userId,
			$container->query('Logger'),
			$this->urlGenerator
		);
	}

	public function tearDown() {
		parent::tearDown();

		$this->clientMapper->deleteAll();
		$this->authorizationCodeMapper->deleteAll();
		$this->accessTokenMapper->deleteAll();
		$this->refreshTokenMapper->deleteAll();
	}

	public function testAddClient() {
		$this->urlGenerator->expects($this->any())->method('linkToRouteAbsolute')->willReturn('/personal');
		$this->clientMapper->deleteAll();

		$result = $this->controller->addClient();
		$this->assertTrue($result instanceof RedirectResponse);
		$this->assertEquals('/personal#' . $this->appName, $result->getRedirectURL());
		$this->assertEquals(0, \count($this->clientMapper->findAll()));

		$_POST['redirect_uri'] = 'test';
		$result = $this->controller->addClient();
		$this->assertTrue($result instanceof RedirectResponse);
		$this->assertEquals('/personal#' . $this->appName, $result->getRedirectURL());
		$this->assertEquals(0, \count($this->clientMapper->findAll()));

		$_POST['redirect_uri'] = null;
		$_POST['name'] = 'test';
		$result = $this->controller->addClient();
		$this->assertTrue($result instanceof RedirectResponse);
		$this->assertEquals('/personal#' . $this->appName, $result->getRedirectURL());
		$this->assertEquals(0, \count($this->clientMapper->findAll()));

		$_POST['redirect_uri'] = 'test';
		$_POST['name'] = 'test';
		$result = $this->controller->addClient();
		$this->assertTrue($result instanceof RedirectResponse);
		$this->assertEquals('/personal#' . $this->appName, $result->getRedirectURL());
		$this->assertEquals(0, \count($this->clientMapper->findAll()));

		$_POST['redirect_uri'] = $this->redirectUri;
		$_POST['name'] = $this->name;
		$result = $this->controller->addClient();
		$this->assertTrue($result instanceof RedirectResponse);
		$this->assertEquals('/personal#' . $this->appName, $result->getRedirectURL());
		$this->assertEquals(1, \count($this->clientMapper->findAll()));
		/** @var Client $client */
		$client = $this->clientMapper->findAll()[0];
		$this->assertEquals($this->redirectUri, $client->getRedirectUri());
		$this->assertEquals($this->name, $client->getName());
		$this->assertEquals(0, $client->getAllowSubdomains());

		$this->clientMapper->delete($client);

		$_POST['allow_subdomains'] = '1';
		$result = $this->controller->addClient();
		$this->assertTrue($result instanceof RedirectResponse);
		$this->assertEquals('/personal#' . $this->appName, $result->getRedirectURL());
		$this->assertEquals(1, \count($this->clientMapper->findAll()));
		/** @var Client $client */
		$client = $this->clientMapper->findAll()[0];
		$this->assertEquals($this->redirectUri, $client->getRedirectUri());
		$this->assertEquals($this->name, $client->getName());
		$this->assertEquals(1, $client->getAllowSubdomains());
	}

	public function testDeleteClient() {
		$this->urlGenerator->expects($this->any())->method('linkToRouteAbsolute')->willReturn('/personal');

		$result = $this->controller->deleteClient(null);
		$this->assertTrue($result instanceof RedirectResponse);
		$this->assertEquals('/personal#' . $this->appName, $result->getRedirectURL());
		$this->assertEquals(1, \count($this->clientMapper->findAll()));

		$result = $this->controller->deleteClient('test');
		$this->assertTrue($result instanceof RedirectResponse);
		$this->assertEquals('/personal#' . $this->appName, $result->getRedirectURL());
		$this->assertEquals(1, \count($this->clientMapper->findAll()));

		$result = $this->controller->deleteClient($this->client->getId());
		$this->assertTrue($result instanceof RedirectResponse);
		$this->assertEquals('/personal#' . $this->appName, $result->getRedirectURL());
		$this->assertEquals(0, \count($this->clientMapper->findAll()));
	}

	public function testRevokeAuthorization() {
		$this->urlGenerator->expects($this->any())->method('linkToRouteAbsolute')->willReturn('/personal');

		$result = $this->controller->revokeAuthorization(null, null);
		$this->assertTrue($result instanceof RedirectResponse);
		$this->assertEquals('/personal#' . $this->appName, $result->getRedirectURL());
		$this->assertEquals(1, \count($this->clientMapper->findAll()));
		$this->assertEquals(1, \count($this->authorizationCodeMapper->findAll()));
		$this->assertEquals(1, \count($this->accessTokenMapper->findAll()));
		$this->assertEquals(1, \count($this->refreshTokenMapper->findAll()));

		$result = $this->controller->revokeAuthorization('', '');
		$this->assertTrue($result instanceof RedirectResponse);
		$this->assertEquals('/personal#' . $this->appName, $result->getRedirectURL());
		$this->assertEquals(1, \count($this->clientMapper->findAll()));
		$this->assertEquals(1, \count($this->authorizationCodeMapper->findAll()));
		$this->assertEquals(1, \count($this->accessTokenMapper->findAll()));
		$this->assertEquals(1, \count($this->refreshTokenMapper->findAll()));

		$result = $this->controller->revokeAuthorization(12, 12);
		$this->assertTrue($result instanceof RedirectResponse);
		$this->assertEquals('/personal#' . $this->appName, $result->getRedirectURL());
		$this->assertEquals(1, \count($this->clientMapper->findAll()));
		$this->assertEquals(1, \count($this->authorizationCodeMapper->findAll()));
		$this->assertEquals(1, \count($this->accessTokenMapper->findAll()));
		$this->assertEquals(1, \count($this->refreshTokenMapper->findAll()));

		$result = $this->controller->revokeAuthorization($this->client->getId(), $this->userId);
		$this->assertTrue($result instanceof RedirectResponse);
		$this->assertEquals('/personal#' . $this->appName, $result->getRedirectURL());
		$this->assertEquals(1, \count($this->clientMapper->findAll()));
		$this->assertEquals(0, \count($this->authorizationCodeMapper->findAll()));
		$this->assertEquals(0, \count($this->accessTokenMapper->findAll()));
		$this->assertEquals(0, \count($this->refreshTokenMapper->findAll()));
	}
}
