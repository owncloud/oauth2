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

namespace OCA\OAuth2\Tests\Unit;

use OC\Core\Application;
use OCA\OAuth2\AuthModule;
use OCA\OAuth2\Db\AccessToken;
use OCA\OAuth2\Db\AccessTokenMapper;
use OCA\OAuth2\Db\Client;
use OCA\OAuth2\Db\ClientMapper;
use PHPUnit_Framework_TestCase;

class AuthModuleTest extends PHPUnit_Framework_TestCase {

	/** @var String $userId */
	private $userId = 'travis';

	/** @var ClientMapper $clientMapper */
	private $clientMapper;

	/** @var AccessTokenMapper */
	private $accessTokenMapper;

	/** @var Client */
	private $client;

	/** @var AccessToken */
	private $accessToken;

	/** @var AuthModule $authModule */
	private $authModule;

	public function setUp() {
		parent::setUp();

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

		$this->authModule = new AuthModule();
	}

	protected function tearDown() {
		parent::tearDown();

		$this->clientMapper->deleteAll();
		$this->accessTokenMapper->deleteAll();
	}

	public function testAuth() {
		// Wrong Authorization header
		$request = $this->getMockBuilder('\OCP\IRequest')->getMock();
		$request->expects($this->once())
			->method('getHeader')
			->with($this->equalTo('Authorization'))
			->will($this->returnValue('Basic sFz6FM9pecGF.62kYz7us43M3am'));
		$this->assertNull($this->authModule->auth($request));

		// Wrong token
		$request = $this->getMockBuilder('\OCP\IRequest')->getMock();
		$request->expects($this->once())
			->method('getHeader')
			->with($this->equalTo('Authorization'))
			->will($this->returnValue('Bearer test'));
		$this->assertNull($this->authModule->auth($request));

		// Expired token
		$this->accessToken->setExpires(time() - 1);
		$this->accessTokenMapper->update($this->accessToken);
		$request = $this->getMockBuilder('\OCP\IRequest')->getMock();
		$request->expects($this->once())
			->method('getHeader')
			->with($this->equalTo('Authorization'))
			->will($this->returnValue('Bearer test'));
		$this->assertNull($this->authModule->auth($request));

		// Valid request
		$this->accessToken->resetExpires();
		$this->accessTokenMapper->update($this->accessToken);
		$request = $this->getMockBuilder('\OCP\IRequest')->getMock();
		$request->expects($this->once())
			->method('getHeader')
			->with($this->equalTo('Authorization'))
			->will($this->returnValue('Bearer sFz6FM9pecGF62kYz7us43M3amqVZaNQZyUZuMIkAJVJaCfVyr4Uf1v2IzvVZXCy'));
		$user = $this->authModule->auth($request);
		$this->assertNotNull($user);
		$this->assertEquals($this->userId, $user->getUID());
	}

	public function testGetUserPassword() {
		$request = $this->getMockBuilder('\OCP\IRequest')->getMock();
		$this->assertEquals('', $this->authModule->getUserPassword($request));
	}

}
