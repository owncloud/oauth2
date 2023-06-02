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
use PHPUnit\Framework\TestCase;

class AccessTokenMapperTest extends TestCase {
	/** @var AccessTokenMapper $accessTokenMapper */
	private $accessTokenMapper;

	/** @var string $userId */
	private $userId = 'john';

	/** @var string $token */
	private $token = '3M3a6FM9pefmkcVyUZuGF62AqVzMJVJaCNXCy4QZIkVZUf1v2IzvsFZaYz7us4yr';

	/** @var int $clientId */
	private $clientId = 1;

	/** @var int $expires */
	private $expires = 12;

	/** @var AccessToken $accessToken1 */
	private $accessToken1;

	/** @var int $id */
	private $id;

	/** @var AccessToken $accessToken2 */
	private $accessToken2;

	public function setUp(): void {
		parent::setUp();

		$app = new Application();
		$container = $app->getContainer();

		$this->accessTokenMapper = $container->query('OCA\OAuth2\Db\AccessTokenMapper');
		$this->accessTokenMapper->deleteAll();

		$accessToken = new AccessToken();
		$accessToken->setToken($this->token);
		$accessToken->setClientId($this->clientId);
		$accessToken->setUserId($this->userId);
		$accessToken->setExpires($this->expires);

		$this->accessToken1 = $this->accessTokenMapper->insert($accessToken);
		$this->id = $this->accessToken1->getId();

		$accessToken = new AccessToken();
		$accessToken->setToken('s4yr3M3VJaCNXCy4QZI7uyUZkVZUf1a6FM9pefmkcVv2IzvsFZaYzuGF62AqVzMJ');
		$accessToken->setClientId(1);
		$accessToken->setUserId('max');
		$accessToken->resetExpires();
		$this->accessToken2 = $this->accessTokenMapper->insert($accessToken);
	}

	public function tearDown(): void {
		parent::tearDown();

		$this->accessTokenMapper->delete($this->accessToken1);
		$this->accessTokenMapper->delete($this->accessToken2);
	}

	public function testFind() {
		/** @var AccessToken $accessToken */
		$accessToken = $this->accessTokenMapper->find($this->id);

		$this->assertEquals($this->id, $accessToken->getId());
		$this->assertEquals($this->token, $accessToken->getToken());
		$this->assertEquals($this->clientId, $accessToken->getClientId());
		$this->assertEquals($this->userId, $accessToken->getUserId());
		$this->assertEquals($this->expires, $accessToken->getExpires());
	}

	/**
	 */
	public function testFindDoesNotExistException() {
		$this->expectException(\OCP\AppFramework\Db\DoesNotExistException::class);

		$this->accessTokenMapper->find(-1);
	}

	/**
	 */
	public function testFindInvalidArgumentException1() {
		$this->expectException(\InvalidArgumentException::class);

		$this->accessTokenMapper->find(null);
	}

	/**
	 */
	public function testFindInvalidArgumentException2() {
		$this->expectException(\InvalidArgumentException::class);

		$this->accessTokenMapper->find('qwertz');
	}

	public function testFindByToken() {
		/** @var AccessToken $accessToken */
		$accessToken = $this->accessTokenMapper->findByToken($this->token);

		$this->assertEquals($this->id, $accessToken->getId());
		$this->assertEquals($this->token, $accessToken->getToken());
		$this->assertEquals($this->clientId, $accessToken->getClientId());
		$this->assertEquals($this->userId, $accessToken->getUserId());
		$this->assertEquals($this->expires, $accessToken->getExpires());
	}

	/**
	 */
	public function testFindByTokenDoesNotExistException() {
		$this->expectException(\OCP\AppFramework\Db\DoesNotExistException::class);

		$this->accessTokenMapper->findByToken('qwertz');
	}

	/**
	 */
	public function testFindByTokenInvalidArgumentException1() {
		$this->expectException(\InvalidArgumentException::class);

		$this->accessTokenMapper->findByToken(null);
	}

	/**
	 */
	public function testFindByTokenInvalidArgumentException2() {
		$this->expectException(\InvalidArgumentException::class);

		$this->accessTokenMapper->findByToken(1);
	}

	public function testFindAll() {
		$accessTokens = $this->accessTokenMapper->findAll();

		$this->assertEquals(2, \count($accessTokens));
	}

	public function testDeleteByClientUser() {
		$this->accessTokenMapper->deleteByClientUser($this->clientId, $this->userId);

		$accessTokens = $this->accessTokenMapper->findAll();
		$this->assertEquals(1, \count($accessTokens));
	}

	/**
	 */
	public function testDeleteByClientUserDoesNotExistException() {
		$this->expectException(\OCP\AppFramework\Db\DoesNotExistException::class);

		$this->accessTokenMapper->deleteByClientUser($this->clientId, $this->userId);
		$this->accessTokenMapper->find($this->id);
	}

	/**
	 */
	public function testDeleteByClientUserInvalidArgumentException1() {
		$this->expectException(\InvalidArgumentException::class);

		$this->accessTokenMapper->deleteByClientUser(null, null);
	}

	/**
	 */
	public function testDeleteByClientUserInvalidArgumentException2() {
		$this->expectException(\InvalidArgumentException::class);

		$this->accessTokenMapper->deleteByClientUser('qwertz', 12);
	}

	/**
	 */
	public function testDeleteByClientUserInvalidArgumentException3() {
		$this->expectException(\InvalidArgumentException::class);

		$this->accessTokenMapper->deleteByClientUser($this->clientId, 12);
	}

	/**
	 */
	public function testDeleteByClientUserInvalidArgumentException4() {
		$this->expectException(\InvalidArgumentException::class);

		$this->accessTokenMapper->deleteByClientUser('qwertz', $this->userId);
	}

	public function testDeleteByClient() {
		$this->accessTokenMapper->deleteByClient($this->clientId);
		$this->assertEquals(0, \count($this->accessTokenMapper->findAll()));
	}

	/**
	 */
	public function testDeleteByClientDoesNotExistException() {
		$this->expectException(\OCP\AppFramework\Db\DoesNotExistException::class);

		$this->accessTokenMapper->deleteByClient($this->clientId);
		$this->accessTokenMapper->find($this->id);
	}

	/**
	 */
	public function testDeleteByClientInvalidArgumentException1() {
		$this->expectException(\InvalidArgumentException::class);

		$this->accessTokenMapper->deleteByClient(null);
	}

	/**
	 */
	public function testDeleteByClientInvalidArgumentException2() {
		$this->expectException(\InvalidArgumentException::class);

		$this->accessTokenMapper->deleteByClient('qwertz');
	}

	public function testDeleteByUser() {
		$this->accessTokenMapper->deleteByUser($this->userId);
		$this->assertEquals(1, \count($this->accessTokenMapper->findAll()));
	}

	/**
	 */
	public function testDeleteByUserDoesNotExistException() {
		$this->expectException(\OCP\AppFramework\Db\DoesNotExistException::class);

		$this->accessTokenMapper->deleteByUser($this->userId);
		$this->accessTokenMapper->find($this->id);
	}

	/**
	 */
	public function testDeleteByUserInvalidArgumentException1() {
		$this->expectException(\InvalidArgumentException::class);

		$this->accessTokenMapper->deleteByUser(null);
	}

	/**
	 */
	public function testDeleteByUserInvalidArgumentException2() {
		$this->expectException(\InvalidArgumentException::class);

		$this->accessTokenMapper->deleteByUser(true);
	}

	public function testDeleteAll() {
		$this->assertEquals(2, \count($this->accessTokenMapper->findAll()));
		$this->accessTokenMapper->deleteAll();
		$this->assertEquals(0, \count($this->accessTokenMapper->findAll()));
	}

	public function testCleanUp() {
		$this->assertEquals(2, \count($this->accessTokenMapper->findAll()));
		$this->accessTokenMapper->cleanUp();
		$this->assertEquals(1, \count($this->accessTokenMapper->findAll()));
		$this->assertEquals($this->accessToken2->getToken(), $this->accessTokenMapper->findAll()[0]->getToken());
	}
}
