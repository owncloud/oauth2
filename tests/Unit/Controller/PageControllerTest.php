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

use OC_Util;
use OCA\OAuth2\AppInfo\Application;
use OCA\OAuth2\Controller\PageController;
use OCA\OAuth2\Db\AccessTokenMapper;
use OCA\OAuth2\Db\AuthorizationCode;
use OCA\OAuth2\Db\AuthorizationCodeMapper;
use OCA\OAuth2\Db\Client;
use OCA\OAuth2\Db\ClientMapper;
use OCA\OAuth2\Db\RefreshTokenMapper;
use OCP\AppFramework\Http\RedirectResponse;
use Test\TestCase;
use OCP\AppFramework\Http\TemplateResponse;

class PageControllerTest extends TestCase {

	/** @var PageController $controller */
	private $controller;

	/** @var ClientMapper $clientMapper */
	private $clientMapper;

	/** @var AuthorizationCodeMapper $authorizationCodeMapper */
	private $authorizationCodeMapper;

	/** @var string $userId */
	private $userId = 'john';

	/** @var string $identifier */
	private $identifier = 'NXCy3M3a6FM9pecVyUZuGF62AJVJaCfmkYz7us4yr4QZqVzMIkVZUf1v2IzvsFZa';

	/** @var string $secret */
	private $secret = '9yUZuGF6pecVaCfmIzvsFZakYNXCyr4QZqVzMIky3M3a6FMz7us4VZUf2AJVJ1v2';

	/** @var string $redirectUri */
	private $redirectUri = 'https://owncloud.org';

	/** @var string $name */
	private $name = 'ownCloud';

	/** @var Client $client */
	private $client;

	public function setUp() {
		$request = $this->getMockBuilder('OCP\IRequest')->getMock();

		$app = new Application();
		$container = $app->getContainer();

		$this->clientMapper = $container->query('OCA\OAuth2\Db\ClientMapper');

		/** @var Client $client */
		$client = new Client();
		$client->setIdentifier($this->identifier);
		$client->setSecret($this->secret);
		$client->setRedirectUri($this->redirectUri);
		$client->setName($this->name);
		$client->setAllowSubdomains(false);
		$this->client = $this->clientMapper->insert($client);

		$this->authorizationCodeMapper = $container->query('OCA\OAuth2\Db\AuthorizationCodeMapper');
		/** @var AccessTokenMapper $accessTokenMapper */
		$accessTokenMapper = $container->query('OCA\OAuth2\Db\AccessTokenMapper');
		/** @var RefreshTokenMapper $refreshTokenMapper */
		$refreshTokenMapper = $container->query('OCA\OAuth2\Db\RefreshTokenMapper');

		$this->controller = new PageController('oauth2', $request, $this->clientMapper, $this->authorizationCodeMapper, $accessTokenMapper, $refreshTokenMapper, $this->userId);
	}

	public function tearDown() {
		$this->clientMapper->delete($this->client);
	}

	public function testAuthorize() {
		// Wrong types
		$result = $this->controller->authorize(1, 'qwertz', 'abcd', 'state');
		$this->assertTrue($result instanceof RedirectResponse);
		$this->assertEquals(OC_Util::getDefaultPageUrl(), $result->getRedirectURL());

		$result = $this->controller->authorize('code', 2, 'abcd', 'state');
		$this->assertTrue($result instanceof RedirectResponse);
		$this->assertEquals(OC_Util::getDefaultPageUrl(), $result->getRedirectURL());

		$result = $this->controller->authorize('code', 'qwertz', 3, 'state');
		$this->assertTrue($result instanceof RedirectResponse);
		$this->assertEquals(OC_Util::getDefaultPageUrl(), $result->getRedirectURL());

		$result = $this->controller->authorize('code', $this->identifier, urldecode($this->redirectUri), 4);
		$this->assertTrue($result instanceof RedirectResponse);
		$this->assertEquals(OC_Util::getDefaultPageUrl(), $result->getRedirectURL());

		// Wrong parameters
		$result = $this->controller->authorize('code', 'qwertz', 'abcd', 'state');
		$this->assertTrue($result instanceof RedirectResponse);
		$this->assertEquals(OC_Util::getDefaultPageUrl(), $result->getRedirectURL());

		$result = $this->controller->authorize('qwertz', $this->identifier, urldecode($this->redirectUri));
		$this->assertTrue($result instanceof RedirectResponse);
		$this->assertEquals(OC_Util::getDefaultPageUrl(), $result->getRedirectURL());

		$result = $this->controller->authorize('code', $this->identifier, urldecode('https://www.example.org'));
		$this->assertTrue($result instanceof RedirectResponse);
		$this->assertEquals(OC_Util::getDefaultPageUrl(), $result->getRedirectURL());

		$result = $this->controller->authorize('code', $this->identifier, urldecode($this->redirectUri));
		$this->assertTrue($result instanceof TemplateResponse);
		$this->assertEquals('authorize', $result->getTemplateName());
		$this->assertEquals(['client_name' => $this->name], $result->getParams());
	}

	public function testGenerateAuthorizationCode() {
		// Wrong types
		$result = $this->controller->generateAuthorizationCode(1, 'qwertz', 'abcd', 'state');
		$this->assertTrue($result instanceof RedirectResponse);
		$this->assertEquals(OC_Util::getDefaultPageUrl(), $result->getRedirectURL());

		$result = $this->controller->generateAuthorizationCode('code', 2, 'abcd', 'state');
		$this->assertTrue($result instanceof RedirectResponse);
		$this->assertEquals(OC_Util::getDefaultPageUrl(), $result->getRedirectURL());

		$result = $this->controller->generateAuthorizationCode('code', 'qwertz', 3, 'state');
		$this->assertTrue($result instanceof RedirectResponse);
		$this->assertEquals(OC_Util::getDefaultPageUrl(), $result->getRedirectURL());

		$result = $this->controller->generateAuthorizationCode('code', $this->identifier, urldecode($this->redirectUri), 4);
		$this->assertTrue($result instanceof RedirectResponse);
		$this->assertEquals(OC_Util::getDefaultPageUrl(), $result->getRedirectURL());

		// Wrong parameters
		$result = $this->controller->generateAuthorizationCode('code', 'qwertz', 'abcd', 'state', 'scope');
		$this->assertTrue($result instanceof RedirectResponse);
		$this->assertEquals(OC_Util::getDefaultPageUrl(), $result->getRedirectURL());

		$result = $this->controller->generateAuthorizationCode('qwertz', $this->identifier, urldecode($this->redirectUri));
		$this->assertTrue($result instanceof RedirectResponse);
		$this->assertEquals(OC_Util::getDefaultPageUrl(), $result->getRedirectURL());

		$result = $this->controller->generateAuthorizationCode('code', $this->identifier, urldecode('https://www.example.org'));
		$this->assertTrue($result instanceof RedirectResponse);
		$this->assertEquals(OC_Util::getDefaultPageUrl(), $result->getRedirectURL());

		$this->assertEquals(0, count($this->authorizationCodeMapper->findAll()));
		$result = $this->controller->generateAuthorizationCode('code', $this->identifier, urldecode($this->redirectUri));
		$this->assertTrue($result instanceof RedirectResponse);
		$this->assertEquals(1, count($this->authorizationCodeMapper->findAll()));
		list($url, $query) = explode('?', $result->getRedirectURL());
		$this->assertEquals($url, $this->redirectUri);
		parse_str($query, $parameters);
		$this->assertTrue(array_key_exists('code', $parameters));
		$expected = time() + 600;
		/** @var AuthorizationCode $authorizationCode */
		$authorizationCode = $this->authorizationCodeMapper->findByCode($parameters['code']);
		$this->assertEquals($expected, $authorizationCode->getExpires(), '', 1);
		$this->assertEquals($this->userId, $authorizationCode->getUserId());
		$this->assertEquals($this->client->getId(), $authorizationCode->getClientId());
		$this->authorizationCodeMapper->delete($this->authorizationCodeMapper->findByCode($parameters['code']));

		$this->assertEquals(0, count($this->authorizationCodeMapper->findAll()));
		$result = $this->controller->generateAuthorizationCode('code', $this->identifier, urldecode($this->redirectUri), 'testingState');
		$this->assertTrue($result instanceof RedirectResponse);
		$this->assertEquals(1, count($this->authorizationCodeMapper->findAll()));
		list($url, $query) = explode('?', $result->getRedirectURL());
		$this->assertEquals($url, $this->redirectUri);
		parse_str($query, $parameters);
		$this->assertTrue(array_key_exists('state', $parameters));
		$this->assertEquals('testingState', $parameters['state']);
		$this->assertTrue(array_key_exists('code', $parameters));
		$expected = time() + 600;
		/** @var AuthorizationCode $authorizationCode */
		$authorizationCode = $this->authorizationCodeMapper->findByCode($parameters['code']);
		$this->assertEquals($expected, $authorizationCode->getExpires(), '', 1);
		$this->assertEquals($this->userId, $authorizationCode->getUserId());
		$this->assertEquals($this->client->getId(), $authorizationCode->getClientId());
		$this->authorizationCodeMapper->delete($this->authorizationCodeMapper->findByCode($parameters['code']));
	}

}
