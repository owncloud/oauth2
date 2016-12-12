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
use OCA\OAuth2\Db\AuthorizationCode;
use OCA\OAuth2\Db\AuthorizationCodeMapper;
use OCP\AppFramework\Db\DoesNotExistException;
use PHPUnit_Framework_TestCase;

class AuthorizationCodeMapperTest extends PHPUnit_Framework_TestCase {

	/** @var AuthorizationCodeMapper $authorizationCodeMapper */
	private $authorizationCodeMapper;

	/** @var string $userId */
	private $userId = 'john';

	/** @var string $code */
	private $code = '3M3a6FM9pefmkcVyUZuGF62AqVzMJVJaCNXCy4QZIkVZUf1v2IzvsFZaYz7us4yr';

	/** @var int $clientId */
	private $clientId = 1;

	/** @var AuthorizationCode $authorizationCode1 */
	private $authorizationCode1;

	/** @var int $id */
	private $id;

	/** @var AuthorizationCode $authorizationCode2 */
	private $authorizationCode2;

	public function setUp() {
		$app = new Application();
		$container = $app->getContainer();

		$this->authorizationCodeMapper = $container->query('AuthorizationCodeMapper');

		$authorizationCode = new AuthorizationCode();
		$authorizationCode->setCode($this->code);
		$authorizationCode->setClientId($this->clientId);
		$authorizationCode->setUserId($this->userId);
		$authorizationCode->setExpires(null);

		$this->authorizationCode1 = $this->authorizationCodeMapper->insert($authorizationCode);
		$this->id = $this->authorizationCode1->getId();

		$authorizationCode = new AuthorizationCode();
		$authorizationCode->setCode('s4yr3M3VJaCNXCy4QZI7uyUZkVZUf1a6FM9pefmkcVv2IzvsFZaYzuGF62AqVzMJ');
		$authorizationCode->setClientId(1);
		$authorizationCode->setUserId('max');
		$authorizationCode->setExpires(null);
		$this->authorizationCode2 = $this->authorizationCodeMapper->insert($authorizationCode);
	}

	public function tearDown() {
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
		$this->assertNull($authorizationCode->getExpires());

		$this->expectException(DoesNotExistException::class);
		$this->authorizationCodeMapper->find(-1);

		$this->expectException(InvalidArgumentException::class);
		$this->authorizationCodeMapper->find(null);
	}

	public function testFindAll() {
		$authorizationCodes = $this->authorizationCodeMapper->findAll();

		$this->assertEquals(2, count($authorizationCodes));
	}

	public function testDeleteByClientUser() {
		$this->authorizationCodeMapper->deleteByClientUser($this->clientId, $this->userId);

		$this->expectException(DoesNotExistException::class);
		$this->authorizationCodeMapper->find($this->id);

		$authorizationCodes = $this->authorizationCodeMapper->findAll();
		$this->assertEquals(1, count($authorizationCodes));

		$this->expectException(InvalidArgumentException::class);
		$this->authorizationCodeMapper->deleteByClientUser(null, null);
		$this->authorizationCodeMapper->deleteByClientUser($this->clientId, null);
		$this->authorizationCodeMapper->deleteByClientUser(null, $this->userId);
	}

}
