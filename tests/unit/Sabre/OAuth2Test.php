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

namespace OCA\OAuth2\Sabre;

use OC\Core\Application;
use OC\User\Session;
use OCA\OAuth2\AuthModule;
use OCA\OAuth2\Db\AccessToken;
use OCA\OAuth2\Db\AccessTokenMapper;
use OCA\OAuth2\Db\Client;
use OCA\OAuth2\Db\ClientMapper;
use OCP\IRequest;
use OCP\ISession;
use OCP\IUser;
use PHPUnit_Framework_MockObject_MockObject;
use Test\TestCase;
use OC\Session\Memory;
use OC\User\User;

/**
 * Class OAuth2Test
 *
 * @package OCA\OAuth2\Sabre
 * @group DB
 */
class OAuth2Test extends TestCase {

	/** @var IRequest | PHPUnit_Framework_MockObject_MockObject $request */
	private $request;

	/** @var String $principalPrefix */
	private $principalPrefix = 'principals/users/';

	/** @var String $userId */
	private $userId = 'john';

	/** @var ClientMapper $clientMapper */
	private $clientMapper;
	/** @var AccessTokenMapper */
	private $accessTokenMapper;
	/** @var Client */
	private $client;
	/** @var AccessToken */
	private $accessToken;
	/** @var IUser | PHPUnit_Framework_MockObject_MockObject */
	private $user;
	/** @var ISession | PHPUnit_Framework_MockObject_MockObject */
	private $session;

	public function setUp() {
		parent::setUp();

		$this->request = $this->createMock(IRequest::class);
		$this->session = $this->createMock(Memory::class);

		$this->user = $this->createMock(User::class);
		$this->user->expects($this->any())
			->method('getUID')
			->willReturn($this->userId);

		$app = new Application();
		$container = $app->getContainer();

		$this->clientMapper = $container->query(ClientMapper::class);
		$this->accessTokenMapper = $container->query(AccessTokenMapper::class);

		$client = new Client();
		$client->setIdentifier('NXCy3M3a6FM9pecVyUZuGF62AJVJaCfmkYz7us4yr4QZqVzMIkVZUf1v2IzvsFZa');
		$client->setSecret('9yUZuGF6pecVaCfmIzvsFZakYNXCyr4QZqVzMIky3M3a6FMz7us4VZUf2AJVJ1v2');
		$client->setRedirectUri('https://owncloud.org');
		$client->setName('ownCloud');
		$this->client = $this->clientMapper->insert($client);

		/** @var AccessToken $accessToken */
		$accessToken = new AccessToken();
		$accessToken->setToken('sFz6FM9pecGF62kYz7us43M3amqVZaNQZyUZuMIkAJVJaCfVyr4Uf1v2IzvVZXCy');
		$accessToken->setClientId($client->getId());
		$accessToken->setUserId($this->userId);
		$accessToken->resetExpires();
		$this->accessToken = $this->accessTokenMapper->insert($accessToken);
	}

	public function providesBearerTokenData() {
		return [
			[true],
			[false]
		];
	}

	protected function tearDown() {
		parent::tearDown();

		$this->clientMapper->deleteAll();
		$this->accessTokenMapper->deleteAll();
	}

	public function testIsDavAuthenticated() {
		// User has not initially authenticated via DAV
		/** @var ISession | PHPUnit_Framework_MockObject_MockObject $session */
		$session = $this->createMock(Memory::class);
		$session->expects($this->any())
			->method('get')
			->with($this->equalTo(OAuth2::DAV_AUTHENTICATED))
			->willReturn(null);
		/** @var Session | PHPUnit_Framework_MockObject_MockObject $userSession */
		$userSession = $this->createMock(Session::class);
		/** @var AuthModule | PHPUnit_Framework_MockObject_MockObject $authModule */
		$authModule = $this->createMock(AuthModule::class);
		$oAuth2 = new OAuth2($session, $userSession, $this->request, $authModule, $this->principalPrefix);
		$this->assertFalse(
			static::invokePrivate(
				$oAuth2,
				'isDavAuthenticated',
				[$this->userId])
		);

		// User has initially authenticated via DAV
		$session = $this->createMock(Memory::class);
		$session->expects($this->any())
			->method('get')
			->with($this->equalTo(OAuth2::DAV_AUTHENTICATED))
			->willReturn($this->userId);
		$userSession = $this->createMock(Session::class);
		/** @var AuthModule | PHPUnit_Framework_MockObject_MockObject $authModule */
		$authModule = $this->createMock(AuthModule::class);
		$oAuth2 = new OAuth2($session, $userSession, $this->request, $authModule, $this->principalPrefix);
		$this->assertTrue(
			static::invokePrivate(
				$oAuth2,
				'isDavAuthenticated',
				[$this->userId])
		);
	}

	/**
	 * @dataProvider providesBearerTokenData
	 * @param bool $invalidToken
	 */
	public function testValidateBearerTokenFailedLogin($invalidToken) {
		/** @var Session | PHPUnit_Framework_MockObject_MockObject $userSession */
		$userSession = $this->createMock(Session::class);
		$userSession->expects($this->any())
			->method('getUser')
			->willReturn($this->user);
		$userSession->expects($this->once())
			->method('isLoggedIn')
			->willReturn(false);
		$this->session->expects($this->once())->method('close');
		if ($invalidToken) {
			$userSession->expects($this->once())
				->method('tryAuthModuleLogin')
				->willReturn(false);
		} else {
			$userSession->expects($this->once())
				->method('tryAuthModuleLogin')
				->willThrowException(new \Exception('Invalid token'));
		}
		/** @var AuthModule | PHPUnit_Framework_MockObject_MockObject $authModule */
		$authModule = $this->createMock(AuthModule::class);
		$oAuth2 = new OAuth2($this->session, $userSession, $this->request, $authModule, $this->principalPrefix);
		$this->assertFalse(
			static::invokePrivate(
				$oAuth2,
				'validateBearerToken',
				[$this->accessToken->getToken()])
		);
	}

	public function testValidateBearerToken() {
		// Successful login
		/** @var Session | PHPUnit_Framework_MockObject_MockObject $userSession */
		$userSession = $this->createMock(Session::class);
		$userSession->expects($this->any())
			->method('getUser')
			->willReturn($this->user);
		$userSession->expects($this->once())
			->method('isLoggedIn')
			->willReturn(false);
		$userSession->expects($this->once())
			->method('tryAuthModuleLogin')
			->willReturn(true);
		/** @var AuthModule | PHPUnit_Framework_MockObject_MockObject $authModule */
		$authModule = $this->createMock(AuthModule::class);
		$oAuth2 = new OAuth2($this->session, $userSession, $this->request, $authModule, $this->principalPrefix);
		$this->assertEquals(
			$this->principalPrefix . $this->userId,
			static::invokePrivate(
				$oAuth2,
				'validateBearerToken',
				[$this->accessToken->getToken()])
		);

		// User has initially authenticated via DAV
		$this->session->expects($this->any())
			->method('get')
			->with($this->equalTo(OAuth2::DAV_AUTHENTICATED))
			->willReturn($this->userId);
		$userSession = $this->createMock(Session::class);
		$userSession->expects($this->any())
			->method('getUser')
			->willReturn($this->user);
		$userSession->expects($this->once())
			->method('isLoggedIn')
			->willReturn(true);

		$john = $this->createMock(IUser::class);
		/** @var AuthModule | PHPUnit_Framework_MockObject_MockObject $authModule */
		$authModule = $this->createMock(AuthModule::class);
		$authModule->expects($this->once())->method('authToken')->willReturn($john);
		$oAuth2 = new OAuth2($this->session, $userSession, $this->request, $authModule, $this->principalPrefix);
		$this->assertEquals(
			$this->principalPrefix . $this->userId,
			static::invokePrivate(
				$oAuth2,
				'validateBearerToken',
				[$this->accessToken->getToken()])
		);
	}

}
