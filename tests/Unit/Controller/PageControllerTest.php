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

use OC_Util;
use OCA\OAuth2\AppInfo\Application;
use OCA\OAuth2\Controller\PageController;
use OCA\OAuth2\Db\AccessTokenMapper;
use OCA\OAuth2\Db\AuthorizationCode;
use OCA\OAuth2\Db\AuthorizationCodeMapper;
use OCA\OAuth2\Db\Client;
use OCA\OAuth2\Db\ClientMapper;
use OCP\AppFramework\Http\RedirectResponse;
use OCP\AppFramework\Http\TemplateResponse;
use OCP\IRequest;
use OCP\IURLGenerator;
use OCP\IUser;
use OCP\IUserManager;
use OCP\IUserSession;
use Test\TestCase;

/**
 * Class PageControllerTest
 *
 * @package OCA\OAuth2\Tests\Unit\Controller
 * @group DB
 */
class PageControllerTest extends TestCase {

	/** @var PageController $controller */
	private $controller;

	/** @var ClientMapper $clientMapper */
	private $clientMapper;

	/** @var AuthorizationCodeMapper $authorizationCodeMapper */
	private $authorizationCodeMapper;

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
		parent::setUp();

		$app = new Application();
		$container = $app->getContainer();

		$this->clientMapper = $container->query(ClientMapper::class);
		$this->clientMapper->deleteAll();

		/** @var Client $client */
		$client = new Client();
		$client->setIdentifier($this->identifier);
		$client->setSecret($this->secret);
		$client->setRedirectUri($this->redirectUri);
		$client->setName($this->name);
		$client->setAllowSubdomains(false);
		$this->client = $this->clientMapper->insert($client);

		$this->authorizationCodeMapper = $container->query(AuthorizationCodeMapper::class);
		/** @var AccessTokenMapper $accessTokenMapper */
		$accessTokenMapper = $container->query(AccessTokenMapper::class);
		/** @var IURLGenerator | \PHPUnit_Framework_MockObject_MockObject $urlGenerator */
		$urlGenerator = $this->createMock(IURLGenerator::class);
		/** @var IUserSession | \PHPUnit_Framework_MockObject_MockObject $userSession */
		$userSession = $this->createMock(IUserSession::class);
		/** @var IRequest | \PHPUnit_Framework_MockObject_MockObject $request */
		$request = $this->createMock(IRequest::class);
		/** @var IUser | \PHPUnit_Framework_MockObject_MockObject $user */
		$user = $this->createMock(IUser::class);
		/** @var IUserManager | \PHPUnit_Framework_MockObject_MockObject $userManager */
		$userManager = $this->createMock(IUserManager::class);
		$userSession->method('getUser')->willReturn($user);
		$userManager->method('get')->willReturn($user);
		$user->method('getUID')->willReturn('Alice');

		$this->controller = new PageController(
			$container->query('AppName'),
			$request,
			$this->clientMapper,
			$this->authorizationCodeMapper,
			$accessTokenMapper,
			$container->query('Logger'),
			$urlGenerator,
			$userSession,
			$userManager
		);
	}

	public function tearDown() {
		parent::tearDown();

		$this->clientMapper->delete($this->client);
	}

	public function testAuthorize() {
		// Wrong types
		$result = $this->controller->authorize(1, 'qwertz', 'abcd', 'state');
		$this->assertInstanceOf(TemplateResponse::class, $result);
		$this->assertEquals('authorize-error', $result->getTemplateName());
		$this->assertEquals(
			['client_name' => null, 'back_url' => OC_Util::getDefaultPageUrl()],
			$result->getParams()
		);

		$result = $this->controller->authorize('code', 2, 'abcd', 'state');
		$this->assertInstanceOf(TemplateResponse::class, $result);
		$this->assertEquals('authorize-error', $result->getTemplateName());
		$this->assertEquals(
			['client_name' => null, 'back_url' => OC_Util::getDefaultPageUrl()],
			$result->getParams()
		);

		$result = $this->controller->authorize('code', 'qwertz', 3, 'state');
		$this->assertInstanceOf(TemplateResponse::class, $result);
		$this->assertEquals('authorize-error', $result->getTemplateName());
		$this->assertEquals(
			['client_name' => null, 'back_url' => OC_Util::getDefaultPageUrl()],
			$result->getParams()
		);

		$result = $this->controller->authorize('code', $this->identifier, \urldecode($this->redirectUri), 4);
		$this->assertInstanceOf(TemplateResponse::class, $result);
		$this->assertEquals('authorize-error', $result->getTemplateName());
		$this->assertEquals(
			['client_name' => null, 'back_url' => OC_Util::getDefaultPageUrl()],
			$result->getParams()
		);

		// Wrong parameters
		$result = $this->controller->authorize('code', 'qwertz', 'abcd', 'state');
		$this->assertInstanceOf(TemplateResponse::class, $result);
		$this->assertEquals('authorize-error', $result->getTemplateName());
		$this->assertEquals(
			['client_name' => null, 'back_url' => OC_Util::getDefaultPageUrl()],
			$result->getParams()
		);

		$result = $this->controller->authorize('qwertz', $this->identifier, \urldecode($this->redirectUri));
		$this->assertInstanceOf(TemplateResponse::class, $result);
		$this->assertEquals('authorize-error', $result->getTemplateName());
		$this->assertEquals(
			['client_name' => $this->name, 'back_url' => OC_Util::getDefaultPageUrl()],
			$result->getParams()
		);

		$result = $this->controller->authorize('code', $this->identifier, \urldecode('https://www.example.org'));
		$this->assertInstanceOf(TemplateResponse::class, $result);
		$this->assertEquals('authorize-error', $result->getTemplateName());
		$this->assertEquals(
			['client_name' => $this->name, 'back_url' => OC_Util::getDefaultPageUrl()],
			$result->getParams()
		);

		$result = $this->controller->authorize('code', $this->identifier, \urldecode($this->redirectUri));
		$this->assertInstanceOf(TemplateResponse::class, $result);
		$this->assertEquals('authorize', $result->getTemplateName());
		$this->assertEquals(['client_name' => $this->name], $result->getParams());
	}

	public function testGenerateAuthorizationCode() {
		// Wrong types
		$result = $this->controller->generateAuthorizationCode(1, 'qwertz', 'abcd', 'state');
		$this->assertInstanceOf(RedirectResponse::class, $result);
		$this->assertEquals(OC_Util::getDefaultPageUrl(), $result->getRedirectURL());

		$result = $this->controller->generateAuthorizationCode('code', 2, 'abcd', 'state');
		$this->assertInstanceOf(RedirectResponse::class, $result);
		$this->assertEquals(OC_Util::getDefaultPageUrl(), $result->getRedirectURL());

		$result = $this->controller->generateAuthorizationCode('code', 'qwertz', 3, 'state');
		$this->assertInstanceOf(RedirectResponse::class, $result);
		$this->assertEquals(OC_Util::getDefaultPageUrl(), $result->getRedirectURL());

		$result = $this->controller->generateAuthorizationCode('code', $this->identifier, \urldecode($this->redirectUri), 4);
		$this->assertInstanceOf(RedirectResponse::class, $result);
		$this->assertEquals(OC_Util::getDefaultPageUrl(), $result->getRedirectURL());

		// Wrong parameters
		$result = $this->controller->generateAuthorizationCode('code', 'qwertz', 'abcd', 'state');
		$this->assertInstanceOf(RedirectResponse::class, $result);
		$this->assertEquals(OC_Util::getDefaultPageUrl(), $result->getRedirectURL());

		$result = $this->controller->generateAuthorizationCode('qwertz', $this->identifier, \urldecode($this->redirectUri));
		$this->assertInstanceOf(RedirectResponse::class, $result);
		$this->assertEquals(OC_Util::getDefaultPageUrl(), $result->getRedirectURL());

		$result = $this->controller->generateAuthorizationCode('code', $this->identifier, \urldecode('https://www.example.org'));
		$this->assertInstanceOf(RedirectResponse::class, $result);
		$this->assertEquals(OC_Util::getDefaultPageUrl(), $result->getRedirectURL());

		$this->assertCount(0, $this->authorizationCodeMapper->findAll());
		$result = $this->controller->generateAuthorizationCode('code', $this->identifier, \urldecode($this->redirectUri));
		$this->assertInstanceOf(RedirectResponse::class, $result);
		$this->assertCount(1, $this->authorizationCodeMapper->findAll());
		list($url, $query) = \explode('?', $result->getRedirectURL());
		$this->assertEquals($url, $this->redirectUri);
		\parse_str($query, $parameters);
		$this->assertTrue(\array_key_exists('code', $parameters));
		$expected = \time() + AuthorizationCode::EXPIRATION_TIME;
		/** @var AuthorizationCode $authorizationCode */
		$authorizationCode = $this->authorizationCodeMapper->findByCode($parameters['code']);
		$this->assertEquals($expected, $authorizationCode->getExpires(), '', 1);
		$this->assertEquals('Alice', $authorizationCode->getUserId());
		$this->assertEquals($this->client->getId(), $authorizationCode->getClientId());
		$this->authorizationCodeMapper->delete($this->authorizationCodeMapper->findByCode($parameters['code']));

		$this->assertCount(0, $this->authorizationCodeMapper->findAll());
		$result = $this->controller->generateAuthorizationCode('code', $this->identifier, \urldecode($this->redirectUri), 'testingState');
		$this->assertInstanceOf(RedirectResponse::class, $result);
		$this->assertCount(1, $this->authorizationCodeMapper->findAll());
		list($url, $query) = \explode('?', $result->getRedirectURL());
		$this->assertEquals($url, $this->redirectUri);
		\parse_str($query, $parameters);
		$this->assertTrue(\array_key_exists('state', $parameters));
		$this->assertEquals('testingState', $parameters['state']);
		$this->assertTrue(\array_key_exists('code', $parameters));
		$expected = \time() + 600;
		/** @var AuthorizationCode $authorizationCode */
		$authorizationCode = $this->authorizationCodeMapper->findByCode($parameters['code']);
		$this->assertEquals($expected, $authorizationCode->getExpires(), '', 1);
		$this->assertEquals('Alice', $authorizationCode->getUserId());
		$this->assertEquals($this->client->getId(), $authorizationCode->getClientId());
		$this->authorizationCodeMapper->delete($this->authorizationCodeMapper->findByCode($parameters['code']));
	}

	public function testAuthorizationSuccessful() {
		$result = $this->controller->authorizationSuccessful();
		$this->assertInstanceOf(TemplateResponse::class, $result);
		$this->assertEquals('authorization-successful', $result->getTemplateName());
	}
}
