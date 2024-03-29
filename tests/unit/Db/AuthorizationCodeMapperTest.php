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
use OCA\OAuth2\Db\AuthorizationCode;
use OCA\OAuth2\Db\AuthorizationCodeMapper;
use PHPUnit\Framework\TestCase;

class AuthorizationCodeMapperTest extends TestCase {
	/** @var AuthorizationCodeMapper $authorizationCodeMapper */
	private $authorizationCodeMapper;

	/** @var string $userId */
	private $userId = 'john';

	/** @var string $code */
	private $code = '3M3a6FM9pefmkcVyUZuGF62AqVzMJVJaCNXCy4QZIkVZUf1v2IzvsFZaYz7us4yr';

	/** @var int $clientId */
	private $clientId = 1;

	/** @var int $expires */
	private $expires = 12;

	/** @var AuthorizationCode $authorizationCode1 */
	private $authorizationCode1;

	/** @var int $id */
	private $id;

	/** @var AuthorizationCode $authorizationCode2 */
	private $authorizationCode2;

	public function setUp(): void {
		parent::setUp();

		$app = new Application();
		$container = $app->getContainer();

		$this->authorizationCodeMapper = $container->query('OCA\OAuth2\Db\AuthorizationCodeMapper');
		$this->authorizationCodeMapper->deleteAll();

		$authorizationCode = new AuthorizationCode();
		$authorizationCode->setCode($this->code);
		$authorizationCode->setClientId($this->clientId);
		$authorizationCode->setUserId($this->userId);
		$authorizationCode->setExpires($this->expires);

		$this->authorizationCode1 = $this->authorizationCodeMapper->insert($authorizationCode);
		$this->id = $this->authorizationCode1->getId();

		$authorizationCode = new AuthorizationCode();
		$authorizationCode->setCode('s4yr3M3VJaCNXCy4QZI7uyUZkVZUf1a6FM9pefmkcVv2IzvsFZaYzuGF62AqVzMJ');
		$authorizationCode->setClientId(1);
		$authorizationCode->setUserId('max');
		$authorizationCode->resetExpires();
		$this->authorizationCode2 = $this->authorizationCodeMapper->insert($authorizationCode);
	}

	public function tearDown(): void {
		parent::tearDown();

		$this->authorizationCodeMapper->delete($this->authorizationCode1);
		$this->authorizationCodeMapper->delete($this->authorizationCode2);
	}

	public function testFind() {
		/** @var AuthorizationCode $authorizationCode */
		$authorizationCode = $this->authorizationCodeMapper->find($this->id);

		$this->assertEquals($this->id, $authorizationCode->getId());
		$this->assertEquals($this->code, $authorizationCode->getCode());
		$this->assertEquals($this->clientId, $authorizationCode->getClientId());
		$this->assertEquals($this->userId, $authorizationCode->getUserId());
		$this->assertEquals($this->expires, $authorizationCode->getExpires());
	}

	/**
	 */
	public function testFindDoesNotExistException() {
		$this->expectException(\OCP\AppFramework\Db\DoesNotExistException::class);

		$this->authorizationCodeMapper->find(-1);
	}

	/**
	 */
	public function testFindInvalidArgumentException1() {
		$this->expectException(\InvalidArgumentException::class);

		$this->authorizationCodeMapper->find(null);
	}

	/**
	 */
	public function testFindInvalidArgumentException2() {
		$this->expectException(\InvalidArgumentException::class);

		$this->authorizationCodeMapper->find('qwertz');
	}

	public function testFindByCode() {
		/** @var AuthorizationCode $authorizationCode */
		$authorizationCode = $this->authorizationCodeMapper->findByCode($this->code);

		$this->assertEquals($this->id, $authorizationCode->getId());
		$this->assertEquals($this->code, $authorizationCode->getCode());
		$this->assertEquals($this->clientId, $authorizationCode->getClientId());
		$this->assertEquals($this->userId, $authorizationCode->getUserId());
		$this->assertEquals($this->expires, $authorizationCode->getExpires());
	}

	/**
	 */
	public function testFindByCodeDoesNotExistException() {
		$this->expectException(\OCP\AppFramework\Db\DoesNotExistException::class);

		$this->authorizationCodeMapper->findByCode('qwertz');
	}

	/**
	 */
	public function testFindByCodeInvalidArgumentException1() {
		$this->expectException(\InvalidArgumentException::class);

		$this->authorizationCodeMapper->findByCode(null);
	}

	/**
	 */
	public function testFindByCodeInvalidArgumentException2() {
		$this->expectException(\InvalidArgumentException::class);

		$this->authorizationCodeMapper->findByCode(1);
	}

	public function testFindAll() {
		$authorizationCodes = $this->authorizationCodeMapper->findAll();

		$this->assertEquals(2, \count($authorizationCodes));
	}

	public function testDeleteByClientUser() {
		$this->authorizationCodeMapper->deleteByClientUser($this->clientId, $this->userId);

		$authorizationCodes = $this->authorizationCodeMapper->findAll();
		$this->assertEquals(1, \count($authorizationCodes));
	}

	/**
	 */
	public function testDeleteByClientUserDoesNotExistException() {
		$this->expectException(\OCP\AppFramework\Db\DoesNotExistException::class);

		$this->authorizationCodeMapper->deleteByClientUser($this->clientId, $this->userId);
		$this->authorizationCodeMapper->find($this->id);
	}

	/**
	 */
	public function testDeleteByClientUserInvalidArgumentException1() {
		$this->expectException(\InvalidArgumentException::class);

		$this->authorizationCodeMapper->deleteByClientUser(null, null);
	}

	/**
	 */
	public function testDeleteByClientUserInvalidArgumentException2() {
		$this->expectException(\InvalidArgumentException::class);

		$this->authorizationCodeMapper->deleteByClientUser('qwertz', 12);
	}

	/**
	 */
	public function testDeleteByClientUserInvalidArgumentException3() {
		$this->expectException(\InvalidArgumentException::class);

		$this->authorizationCodeMapper->deleteByClientUser($this->clientId, 12);
	}

	/**
	 */
	public function testDeleteByClientUserInvalidArgumentException4() {
		$this->expectException(\InvalidArgumentException::class);

		$this->authorizationCodeMapper->deleteByClientUser('qwertz', $this->userId);
	}

	public function testDeleteByClient() {
		$this->authorizationCodeMapper->deleteByClient($this->clientId);
		$this->assertEquals(0, \count($this->authorizationCodeMapper->findAll()));
	}

	/**
	 */
	public function testDeleteByClientDoesNotExistException() {
		$this->expectException(\OCP\AppFramework\Db\DoesNotExistException::class);

		$this->authorizationCodeMapper->deleteByClient($this->clientId);
		$this->authorizationCodeMapper->find($this->id);
	}

	/**
	 */
	public function testDeleteByClientInvalidArgumentException1() {
		$this->expectException(\InvalidArgumentException::class);

		$this->authorizationCodeMapper->deleteByClient(null);
	}

	/**
	 */
	public function testDeleteByClientInvalidArgumentException2() {
		$this->expectException(\InvalidArgumentException::class);

		$this->authorizationCodeMapper->deleteByClient('qwertz');
	}

	public function testDeleteByUser() {
		$this->authorizationCodeMapper->deleteByUser($this->userId);
		$this->assertEquals(1, \count($this->authorizationCodeMapper->findAll()));
	}

	/**
	 */
	public function testDeleteByUserDoesNotExistException() {
		$this->expectException(\OCP\AppFramework\Db\DoesNotExistException::class);

		$this->authorizationCodeMapper->deleteByUser($this->userId);
		$this->authorizationCodeMapper->find($this->id);
	}

	/**
	 */
	public function testDeleteByUserInvalidArgumentException1() {
		$this->expectException(\InvalidArgumentException::class);

		$this->authorizationCodeMapper->deleteByUser(null);
	}

	/**
	 */
	public function testDeleteByUserInvalidArgumentException2() {
		$this->expectException(\InvalidArgumentException::class);

		$this->authorizationCodeMapper->deleteByUser(true);
	}

	public function testDeleteAll() {
		$this->assertEquals(2, \count($this->authorizationCodeMapper->findAll()));
		$this->authorizationCodeMapper->deleteAll();
		$this->assertEquals(0, \count($this->authorizationCodeMapper->findAll()));
	}

	public function testCleanUp() {
		$this->assertEquals(2, \count($this->authorizationCodeMapper->findAll()));
		$this->authorizationCodeMapper->cleanUp();
		$this->assertEquals(1, \count($this->authorizationCodeMapper->findAll()));
		$this->assertEquals($this->authorizationCode2->getCode(), $this->authorizationCodeMapper->findAll()[0]->getCode());
	}
}
