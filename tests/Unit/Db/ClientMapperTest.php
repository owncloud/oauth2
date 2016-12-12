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
use OCA\OAuth2\Db\AuthorizationCode;
use OCA\OAuth2\Db\AuthorizationCodeMapper;
use OCA\OAuth2\Db\Client;
use OCA\OAuth2\Db\ClientMapper;
use OCP\AppFramework\Db\DoesNotExistException;
use PHPUnit_Framework_TestCase;

class ClientMapperTest extends PHPUnit_Framework_TestCase {

	/** @var ClientMapper $clientMapper */
	private $clientMapper;

	/** @var string $identifier */
	private $identifier = 'NXCy3M3a6FM9pecVyUZuGF62AJVJaCfmkYz7us4yr4QZqVzMIkVZUf1v2IzvsFZa';

	/** @var string $secret */
	private $secret = '9yUZuGF6pecVaCfmIzvsFZakYNXCyr4QZqVzMIky3M3a6FMz7us4VZUf2AJVJ1v2';

	/** @var string $redirectUri */
	private $redirectUri = 'https://owncloud.org';

	/** @var string $name */
	private $name = 'ownCloud';

	/** @var Client $client1 */
	private $client1;

	/** @var int $id */
	private $id;

	/** @var Client $client2 */
	private $client2;

	/** @var string $userId */
	private $userId = 'john';

	/** @var AuthorizationCodeMapper $authorizationCodeMapper */
	private $authorizationCodeMapper;

	/** @var AuthorizationCode $authorizationCode */
	private $authorizationCode;

	/** @var AccessTokenMapper $accessTokenMapper */
	private $accessTokenMapper;

	/** @var AccessToken $accessToken */
	private $accessToken;

	public function setUp() {
		$app = new Application();
		$container = $app->getContainer();

		$this->clientMapper = $container->query('ClientMapper');

		$client = new Client();
		$client->setIdentifier($this->identifier);
		$client->setSecret($this->secret);
		$client->setRedirectUri($this->redirectUri);
		$client->setName($this->name);

		$this->client1 = $this->clientMapper->insert($client);
		$this->id = $this->client1->getId();

		$client = new Client();
		$client->setIdentifier('uGF62As4yr4QZqVz3a6FM9peJVJaCfmkYz7ucVyUZZUf1v2IzvsFZaMIkVNXCy3M');
		$client->setSecret('z7us4VZUf2fmIzvsFZakYNXCyrky3M39yUZuGF6pecVaCa6FMAJVJ1v24QZqVzMI');
		$client->setRedirectUri('https://www.google.de');
		$client->setName('Google');
		$this->client2 = $this->clientMapper->insert($client);

		$this->authorizationCodeMapper = $container->query('AuthorizationCodeMapper');
		$this->accessTokenMapper = $container->query('AccessTokenMapper');

		$authorizationCode = new AuthorizationCode();
		$authorizationCode->setCode('akYNVaCz7us4VZUf2f24QZqXCyrky3M39yUZuGF6pecVzMImIzvsFZa6FMAJVJ1v');
		$authorizationCode->setClientId($this->id);
		$authorizationCode->setUserId($this->userId);
		$this->authorizationCode = $this->authorizationCodeMapper->insert($authorizationCode);

		$accessToken = new AccessToken();
		$accessToken->setToken('qXF6pecVzMf2f24QZIzvImakYNVaCz7ussFZa6FMAJVJ1vCyrky3M39yUZuG4VZU');
		$accessToken->setClientId($this->id);
		$accessToken->setUserId($this->userId);
		$this->accessToken = $this->accessTokenMapper->insert($accessToken);
	}

	public function tearDown() {
		$this->clientMapper->delete($this->client1);
		$this->clientMapper->delete($this->client2);
		$this->authorizationCodeMapper->delete($this->authorizationCode);
		$this->accessTokenMapper->delete($this->accessToken);
	}

	public function testFind() {
		$client = $this->clientMapper->find($this->id);

		$this->assertEquals($this->id, $client->getId());
		$this->assertEquals($this->identifier, $client->getIdentifier());
		$this->assertEquals($this->secret, $client->getSecret());
		$this->assertEquals($this->redirectUri, $client->getRedirectUri());
		$this->assertEquals($this->name, $client->getName());

		$this->expectException(DoesNotExistException::class);
		$this->clientMapper->find(-1);

		$this->expectException(InvalidArgumentException::class);
		$this->clientMapper->find(null);
	}

	public function testFindByIdentifier() {
		$client = $this->clientMapper->findByIdentifier($this->identifier);

		$this->assertEquals($this->id, $client->getId());
		$this->assertEquals($this->identifier, $client->getIdentifier());
		$this->assertEquals($this->secret, $client->getSecret());
		$this->assertEquals($this->redirectUri, $client->getRedirectUri());
		$this->assertEquals($this->name, $client->getName());

		$this->expectException(DoesNotExistException::class);
		$this->clientMapper->findByIdentifier('qwertz');

		$this->expectException(InvalidArgumentException::class);
		$this->clientMapper->find(null);
	}

	public function testFindAll() {
		$clients = $this->clientMapper->findAll();

		$this->assertEquals(2, count($clients));
	}

	public function testFindByUser() {
		$clients = $this->clientMapper->findByUser($this->userId);

		$this->assertEquals(1, count($clients));

		$client = $clients[0];
		$this->assertEquals($this->id, $client->getId());
		$this->assertEquals($this->identifier, $client->getIdentifier());
		$this->assertEquals($this->secret, $client->getSecret());
		$this->assertEquals($this->redirectUri, $client->getRedirectUri());
		$this->assertEquals($this->name, $client->getName());

		$clients = $this->clientMapper->findByUser('qwertz');
		$this->assertEmpty($clients);

		$this->expectException(InvalidArgumentException::class);
		$this->clientMapper->findByUser(null);
	}

}
