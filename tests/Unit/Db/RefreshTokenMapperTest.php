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

namespace OCA\OAuth2\Tests\Unit\Db;

use InvalidArgumentException;
use OCA\OAuth2\AppInfo\Application;
use OCA\OAuth2\Db\RefreshToken;
use OCA\OAuth2\Db\RefreshTokenMapper;
use OCP\AppFramework\Db\DoesNotExistException;
use PHPUnit_Framework_TestCase;

class RefreshTokenMapperTest extends PHPUnit_Framework_TestCase {

	/** @var RefreshTokenMapper $refreshTokenMapper */
	private $refreshTokenMapper;

	/** @var string $userId */
	private $userId = 'john';

	/** @var string $token */
	private $token = 'uG4QZIkVZUf13M3a6FM9pefmkcVyU4yrF62AqVzMJVJaZv2IzvsFZaYz7usCNXCy';

	/** @var int $clientId */
	private $clientId = 1;

	/** @var RefreshToken $refreshToken1 */
	private $refreshToken1;

	/** @var int $id */
	private $id;

	/** @var RefreshToken $refreshToken2 */
	private $refreshToken2;

	public function setUp() {
		$app = new Application();
		$container = $app->getContainer();

		$this->refreshTokenMapper = $container->query('OCA\OAuth2\Db\RefreshTokenMapper');

		$refreshToken = new RefreshToken();
		$refreshToken->setToken($this->token);
		$refreshToken->setClientId($this->clientId);
		$refreshToken->setUserId($this->userId);
		$refreshToken->setExpires(null);

		$this->refreshToken1 = $this->refreshTokenMapper->insert($refreshToken);
		$this->id = $this->refreshToken1->getId();

		$refreshToken = new RefreshToken();
		$refreshToken->setToken('XCy4QZI7s4yr3MmkcVv2IzvkVZUf1asFZaYzuGF6uyUZ6FM9pef2AqVzMJ3VJaCN');
		$refreshToken->setClientId(1);
		$refreshToken->setUserId('max');
		$refreshToken->setExpires(null);
		$this->refreshToken2 = $this->refreshTokenMapper->insert($refreshToken);
	}

	public function tearDown() {
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
		$this->assertNull($refreshToken->getExpires());

		$this->expectException(DoesNotExistException::class);
		$this->refreshTokenMapper->find(-1);

		$this->expectException(InvalidArgumentException::class);
		$this->refreshTokenMapper->find(null);
	}

	public function testFindAll() {
		$refreshTokens = $this->refreshTokenMapper->findAll();

		$this->assertEquals(2, count($refreshTokens));
	}

	public function testDeleteByClientUser() {
		$this->refreshTokenMapper->deleteByClientUser($this->clientId, $this->userId);

		$this->expectException(DoesNotExistException::class);
		$this->refreshTokenMapper->find($this->id);

		$refreshTokens = $this->refreshTokenMapper->findAll();
		$this->assertEquals(1, count($refreshTokens));

		$this->expectException(InvalidArgumentException::class);
		$this->refreshTokenMapper->deleteByClientUser(null, null);
		$this->refreshTokenMapper->deleteByClientUser($this->clientId, null);
		$this->refreshTokenMapper->deleteByClientUser(null, $this->userId);
	}

}
