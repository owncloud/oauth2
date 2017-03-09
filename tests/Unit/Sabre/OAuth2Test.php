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

namespace OCA\OAuth2\Tests\Unit\Sabre;

use OC\Core\Application;
use OC\User\Session;
use OCA\OAuth2\Db\AccessToken;
use OCA\OAuth2\Db\AccessTokenMapper;
use OCA\OAuth2\Db\Client;
use OCA\OAuth2\Db\ClientMapper;
use OCA\OAuth2\Sabre\OAuth2;
use OCP\IRequest;
use OCP\ISession;
use PHPUnit_Framework_MockObject_MockObject;
use Test\TestCase;

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

	public function setUp() {
		parent::setUp();

		$this->request = $this->createMock('\OCP\IRequest');

		$app = new Application();
		$container = $app->getContainer();

		$this->clientMapper = $container->query('OCA\OAuth2\Db\ClientMapper');
		$this->accessTokenMapper = $container->query('OCA\OAuth2\Db\AccessTokenMapper');

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

	protected function tearDown() {
		parent::tearDown();

		$this->clientMapper->deleteAll();
		$this->accessTokenMapper->deleteAll();
	}

	public function testIsDavAuthenticated() {
		// User has not initially authenticated via DAV
		/** @var ISession | PHPUnit_Framework_MockObject_MockObject $session */
		$session = $this->createMock('\OC\Session\Memory');
		$session->expects($this->any())
			->method('get')
			->with($this->equalTo(OAuth2::DAV_AUTHENTICATED))
			->will($this->returnValue(null));
		/** @var Session | PHPUnit_Framework_MockObject_MockObject $userSession */
		$userSession = $this->createMock('\OC\User\Session');
		$oAuth2 = new OAuth2($session, $userSession, $this->request, $this->principalPrefix);
		$this->assertFalse(
			$this->invokePrivate(
				$oAuth2,
				'isDavAuthenticated',
				[$this->userId])
		);

		// User has initially authenticated via DAV
		$session = $this->createMock('\OC\Session\Memory');
		$session->expects($this->any())
			->method('get')
			->with($this->equalTo(OAuth2::DAV_AUTHENTICATED))
			->will($this->returnValue($this->userId));
		$userSession = $this->createMock('\OC\User\Session');
		$oAuth2 = new OAuth2($session, $userSession, $this->request, $this->principalPrefix);
		$this->assertTrue(
			$this->invokePrivate(
				$oAuth2,
				'isDavAuthenticated',
				[$this->userId])
		);
	}

	public function testValidateBearerToken() {
		/** @var ISession | PHPUnit_Framework_MockObject_MockObject $session */
		$session = $this->createMock('\OC\Session\Memory');
		$user = $this->createMock('\OC\User\User');
		$user->expects($this->any())
			->method('getUID')
			->will($this->returnValue($this->userId));

		// Failing Login
		/** @var Session | PHPUnit_Framework_MockObject_MockObject $userSession */
		$userSession = $this->createMock('\OC\User\Session');
		$userSession->expects($this->any())
			->method('getUser')
			->will($this->returnValue($user));
		$userSession->expects($this->once())
			->method('isLoggedIn')
			->will($this->returnValue(false));
		$userSession->expects($this->once())
			->method('tryAuthModuleLogin')
			->will($this->returnValue(false));
		$oAuth2 = new OAuth2($session, $userSession, $this->request, $this->principalPrefix);
		$this->assertFalse(
			$this->invokePrivate(
				$oAuth2,
				'validateBearerToken',
				[$this->accessToken->getToken()])
		);

		// Successful login
		$userSession = $this->createMock('\OC\User\Session');
		$userSession->expects($this->any())
			->method('getUser')
			->will($this->returnValue($user));
		$userSession->expects($this->once())
			->method('isLoggedIn')
			->will($this->returnValue(false));
		$userSession->expects($this->once())
			->method('tryAuthModuleLogin')
			->will($this->returnValue(true));
		$oAuth2 = new OAuth2($session, $userSession, $this->request, $this->principalPrefix);
		$this->assertEquals(
			$this->principalPrefix . $this->userId,
			$this->invokePrivate(
				$oAuth2,
				'validateBearerToken',
				[$this->accessToken->getToken()])
		);

		// User has initially authenticated via DAV
		$session = $this->createMock('\OC\Session\Memory');
		$session->expects($this->any())
			->method('get')
			->with($this->equalTo(OAuth2::DAV_AUTHENTICATED))
			->will($this->returnValue($this->userId));
		$userSession = $this->createMock('\OC\User\Session');
		$userSession->expects($this->any())
			->method('getUser')
			->will($this->returnValue($user));
		$userSession->expects($this->once())
			->method('isLoggedIn')
			->will($this->returnValue(true));
		$oAuth2 = new OAuth2($session, $userSession, $this->request, $this->principalPrefix);
		$this->assertEquals(
			$this->principalPrefix . $this->userId,
			$this->invokePrivate(
				$oAuth2,
				'validateBearerToken',
				[$this->accessToken->getToken()])
		);
	}

}
