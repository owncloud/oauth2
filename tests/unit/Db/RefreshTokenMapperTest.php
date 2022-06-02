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

namespace OCA\OAuth2\Tests\Unit\Db;

use OCA\OAuth2\AppInfo\Application;
use OCA\OAuth2\Db\AccessToken;
use OCA\OAuth2\Db\AccessTokenMapper;
use OCA\OAuth2\Db\RefreshToken;
use OCA\OAuth2\Db\RefreshTokenMapper;
use PHPUnit\Framework\TestCase;

class RefreshTokenMapperTest extends TestCase {

	/** @var RefreshTokenMapper $refreshTokenMapper */
	private $refreshTokenMapper;

	/** @var string $userId */
	private $userId = 'john';

	/** @var string $token */
	private $token = 'uG4QZIkVZUf13M3a6FM9pefmkcVyU4yrF62AqVzMJVJaZv2IzvsFZaYz7usCNXCy';

	/** @var int $clientId */
	private $clientId = 1;

	/** @var AccessToken $accessToken1 */
	private $accessToken1;

	/** @var RefreshToken $refreshToken1 */
	private $refreshToken1;

	/** @var int $id */
	private $id;

	/** @var AccessToken $accessToken2 */
	private $accessToken2;

	/** @var RefreshToken $refreshToken2 */
	private $refreshToken2;

	/** @var AccessTokenMapper */
	private $accessTokenMapper;

	public function setUp(): void {
		parent::setUp();

		$app = new Application();
		$container = $app->getContainer();

		$this->refreshTokenMapper = $container->query('OCA\OAuth2\Db\RefreshTokenMapper');
		$this->refreshTokenMapper->deleteAll();

		$this->accessTokenMapper = $container->query(AccessTokenMapper::class);

		$accessToken = new AccessToken();
		$accessToken->setToken('3M3amqVGF62kYz7us4yr4QZyUZuMIAZUf1v2IzvsFJVJaCfz6FM9pecVkVZaNXCy');
		$accessToken->setClientId($this->clientId);
		$accessToken->setUserId($this->userId);
		$accessToken->resetExpires();
		$this->accessToken1 = $this->accessTokenMapper->insert($accessToken);

		$refreshToken = new RefreshToken();
		$refreshToken->setToken($this->token);
		$refreshToken->setClientId($this->clientId);
		$refreshToken->setUserId($this->userId);
		$refreshToken->setAccessTokenId($accessToken->getId());

		$this->refreshToken1 = $this->refreshTokenMapper->insert($refreshToken);
		$this->id = $this->refreshToken1->getId();

		$accessToken2 = new AccessToken();
		$accessToken2->setToken('vH5Z4r4af6wBvrtKS2Px1tLI9eIsJQUjGjAMJcSALzIwqL7YVnyMltYHAGOiLpV8');
		$accessToken2->setClientId($this->clientId);
		$accessToken2->setUserId($this->userId);
		$accessToken2->resetExpires();
		$this->accessToken2 = $this->accessTokenMapper->insert($accessToken2);

		$refreshToken2 = new RefreshToken();
		$refreshToken2->setToken('XCy4QZI7s4yr3MmkcVv2IzvkVZUf1asFZaYzuGF6uyUZ6FM9pef2AqVzMJ3VJaCN');
		$refreshToken2->setClientId(1);
		$refreshToken2->setUserId('max');
		$refreshToken2->setAccessTokenId($this->accessToken2->getId());
		$this->refreshToken2 = $this->refreshTokenMapper->insert($refreshToken2);
	}

	public function tearDown(): void {
		parent::tearDown();

		$this->accessTokenMapper->delete($this->accessToken1);
		$this->accessTokenMapper->delete($this->accessToken2);
		$this->refreshTokenMapper->delete($this->refreshToken1);
		$this->refreshTokenMapper->delete($this->refreshToken2);
	}

	public function testFind() {
		/** @var RefreshToken $refreshToken */
		$refreshToken = $this->refreshTokenMapper->find($this->id);

		$this->assertEquals($this->id, $refreshToken->getId());
		$this->assertEquals($this->token, $refreshToken->getToken());
		$this->assertEquals($this->clientId, $refreshToken->getClientId());
		$this->assertEquals($this->userId, $refreshToken->getUserId());
	}

	/**
	 */
	public function testFindDoesNotExistException() {
		$this->expectException(\OCP\AppFramework\Db\DoesNotExistException::class);

		$this->refreshTokenMapper->find(-1);
	}

	/**
	 */
	public function testFindInvalidArgumentException1() {
		$this->expectException(\InvalidArgumentException::class);

		$this->refreshTokenMapper->find(null);
	}

	/**
	 */
	public function testFindInvalidArgumentException2() {
		$this->expectException(\InvalidArgumentException::class);

		$this->refreshTokenMapper->find('qwertz');
	}

	public function testFindByToken() {
		/** @var RefreshToken $refreshToken */
		$refreshToken = $this->refreshTokenMapper->findByToken($this->token);

		$this->assertEquals($this->id, $refreshToken->getId());
		$this->assertEquals($this->token, $refreshToken->getToken());
		$this->assertEquals($this->clientId, $refreshToken->getClientId());
		$this->assertEquals($this->userId, $refreshToken->getUserId());
	}

	/**
	 */
	public function testFindByTokenDoesNotExistException() {
		$this->expectException(\OCP\AppFramework\Db\DoesNotExistException::class);

		$this->refreshTokenMapper->findByToken('qwertz');
	}

	/**
	 */
	public function testFindByTokenInvalidArgumentException1() {
		$this->expectException(\InvalidArgumentException::class);

		$this->refreshTokenMapper->findByToken(null);
	}

	/**
	 */
	public function testFindByTokenInvalidArgumentException2() {
		$this->expectException(\InvalidArgumentException::class);

		$this->refreshTokenMapper->findByToken(1);
	}

	public function testFindAll() {
		$refreshTokens = $this->refreshTokenMapper->findAll();

		$this->assertEquals(2, \count($refreshTokens));
	}

	public function testDeleteByClientUser() {
		$this->refreshTokenMapper->deleteByClientUser($this->clientId, $this->userId);

		$refreshTokens = $this->refreshTokenMapper->findAll();
		$this->assertEquals(1, \count($refreshTokens));
	}

	/**
	 */
	public function testDeleteByClientUserDoesNotExistException() {
		$this->expectException(\OCP\AppFramework\Db\DoesNotExistException::class);

		$this->refreshTokenMapper->deleteByClientUser($this->clientId, $this->userId);
		$this->refreshTokenMapper->find($this->id);
	}

	/**
	 */
	public function testDeleteByClientUserInvalidArgumentException1() {
		$this->expectException(\InvalidArgumentException::class);

		$this->refreshTokenMapper->deleteByClientUser(null, null);
	}

	/**
	 */
	public function testDeleteByClientUserInvalidArgumentException2() {
		$this->expectException(\InvalidArgumentException::class);

		$this->refreshTokenMapper->deleteByClientUser('qwertz', 12);
	}

	/**
	 */
	public function testDeleteByClientUserInvalidArgumentException3() {
		$this->expectException(\InvalidArgumentException::class);

		$this->refreshTokenMapper->deleteByClientUser($this->clientId, 12);
	}

	/**
	 */
	public function testDeleteByClientUserInvalidArgumentException4() {
		$this->expectException(\InvalidArgumentException::class);

		$this->refreshTokenMapper->deleteByClientUser('qwertz', $this->userId);
	}

	public function testDeleteByClient() {
		$this->refreshTokenMapper->deleteByClient($this->clientId);
		$this->assertEquals(0, \count($this->refreshTokenMapper->findAll()));
	}

	/**
	 */
	public function testDeleteByClientDoesNotExistException() {
		$this->expectException(\OCP\AppFramework\Db\DoesNotExistException::class);

		$this->refreshTokenMapper->deleteByClient($this->clientId);
		$this->refreshTokenMapper->find($this->id);
	}

	/**
	 */
	public function testDeleteByClientInvalidArgumentException1() {
		$this->expectException(\InvalidArgumentException::class);

		$this->refreshTokenMapper->deleteByClient(null);
	}

	/**
	 */
	public function testDeleteByClientInvalidArgumentException2() {
		$this->expectException(\InvalidArgumentException::class);

		$this->refreshTokenMapper->deleteByClient('qwertz');
	}

	public function testDeleteByUser() {
		$this->refreshTokenMapper->deleteByUser($this->userId);
		$this->assertEquals(1, \count($this->refreshTokenMapper->findAll()));
	}

	/**
	 */
	public function testDeleteByUserDoesNotExistException() {
		$this->expectException(\OCP\AppFramework\Db\DoesNotExistException::class);

		$this->refreshTokenMapper->deleteByUser($this->userId);
		$this->refreshTokenMapper->find($this->id);
	}

	/**
	 */
	public function testDeleteByUserInvalidArgumentException1() {
		$this->expectException(\InvalidArgumentException::class);

		$this->refreshTokenMapper->deleteByUser(null);
	}

	/**
	 */
	public function testDeleteByUserInvalidArgumentException2() {
		$this->expectException(\InvalidArgumentException::class);

		$this->refreshTokenMapper->deleteByUser(true);
	}

	public function testDeleteAll() {
		$this->assertEquals(2, \count($this->refreshTokenMapper->findAll()));
		$this->refreshTokenMapper->deleteAll();
		$this->assertEquals(0, \count($this->refreshTokenMapper->findAll()));
	}
}
