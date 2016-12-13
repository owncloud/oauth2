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
use OCA\OAuth2\Db\AccessToken;
use OCA\OAuth2\Db\AccessTokenMapper;
use OCP\AppFramework\Db\DoesNotExistException;
use PHPUnit_Framework_TestCase;

class AccessTokenMapperTest extends PHPUnit_Framework_TestCase {

	/** @var AccessTokenMapper $accessTokenMapper */
	private $accessTokenMapper;

	/** @var string $userId */
	private $userId = 'john';

	/** @var string $token */
	private $token = '3M3a6FM9pefmkcVyUZuGF62AqVzMJVJaCNXCy4QZIkVZUf1v2IzvsFZaYz7us4yr';

	/** @var int $clientId */
	private $clientId = 1;

	/** @var AccessToken $accessToken1 */
	private $accessToken1;

	/** @var int $id */
	private $id;

	/** @var AccessToken $accessToken2 */
	private $accessToken2;

	public function setUp() {
		$app = new Application();
		$container = $app->getContainer();

		$this->accessTokenMapper = $container->query('OCA\OAuth2\Db\AccessTokenMapper');

		$accessToken = new AccessToken();
		$accessToken->setToken($this->token);
		$accessToken->setClientId($this->clientId);
		$accessToken->setUserId($this->userId);
		$accessToken->setExpires(null);

		$this->accessToken1 = $this->accessTokenMapper->insert($accessToken);
		$this->id = $this->accessToken1->getId();

		$accessToken = new AccessToken();
		$accessToken->setToken('s4yr3M3VJaCNXCy4QZI7uyUZkVZUf1a6FM9pefmkcVv2IzvsFZaYzuGF62AqVzMJ');
		$accessToken->setClientId(1);
		$accessToken->setUserId('max');
		$accessToken->setExpires(null);
		$this->accessToken2 = $this->accessTokenMapper->insert($accessToken);
	}

	public function tearDown() {
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
		$this->assertNull($accessToken->getExpires());

		$this->expectException(DoesNotExistException::class);
		$this->accessTokenMapper->find(-1);

		$this->expectException(InvalidArgumentException::class);
		$this->accessTokenMapper->find(null);
	}

	public function testFindAll() {
		$accessTokens = $this->accessTokenMapper->findAll();

		$this->assertEquals(2, count($accessTokens));
	}

	public function testDeleteByClientUser() {
		$this->accessTokenMapper->deleteByClientUser($this->clientId, $this->userId);

		$this->expectException(DoesNotExistException::class);
		$this->accessTokenMapper->find($this->id);

		$accessTokens = $this->accessTokenMapper->findAll();
		$this->assertEquals(1, count($accessTokens));

		$this->expectException(InvalidArgumentException::class);
		$this->accessTokenMapper->deleteByClientUser(null, null);
		$this->accessTokenMapper->deleteByClientUser($this->clientId, null);
		$this->accessTokenMapper->deleteByClientUser(null, $this->userId);
	}

}
