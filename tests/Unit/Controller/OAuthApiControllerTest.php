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

namespace OCA\OAuth2\Tests\Unit\Controller;

use OCA\OAuth2\Controller\OAuthApiController;
use OCA\OAuth2\Db\AccessTokenMapper;
use OCA\OAuth2\Db\AuthorizationCode;
use OCA\OAuth2\Db\AuthorizationCodeMapper;
use OCA\OAuth2\Db\Client;
use OCA\OAuth2\Db\ClientMapper;
use OCP\AppFramework\Http\JSONResponse;
use PHPUnit_Framework_TestCase;
use OCA\OAuth2\AppInfo\Application;

class OAuthApiControllerTest extends PHPUnit_Framework_TestCase {

	/** @var OAuthApiController $controller */
	private $controller;

	/** @var ClientMapper $clientMapper */
	private $clientMapper;

	/** @var AuthorizationCodeMapper $authorizationCodeMapper */
	private $authorizationCodeMapper;

	/** @var AccessTokenMapper */
	private $accessTokenMapper;

	/** @var string $userId */
	private $userId = 'john';

	/** @var string $clientIdentifier1 */
	private $clientIdentifier1 = 'NXCy3M3a6FM9pecVyUZuGF62AJVJaCfmkYz7us4yr4QZqVzMIkVZUf1v2IzvsFZa';

	/** @var string $clientIdentifier2 */
	private $clientIdentifier2 = 'JaCfmkYz7MIkXCy3M3a6FM9pecVyUZVZUf1v2IzvsFZaNuGF62AJVus4yr4QZqVz';

	/** @var string $clientSecret */
	private $clientSecret = '9yUZuGF6pecVaCfmIzvsFZakYNXCyr4QZqVzMIky3M3a6FMz7us4VZUf2AJVJ1v2';

	/** @var Client $client1 */
	private $client1;

	/** @var Client $client2 */
	private $client2;

	/** @var AuthorizationCode $authorizationCode */
	private $authorizationCode;

	public function setUp() {
		$request = $this->getMockBuilder('OCP\IRequest')->getMock();

		$app = new Application();
		$container = $app->getContainer();

		$this->clientMapper = $container->query('OCA\OAuth2\Db\ClientMapper');
		$this->authorizationCodeMapper = $container->query('OCA\OAuth2\Db\AuthorizationCodeMapper');
		$this->accessTokenMapper = $container->query('OCA\OAuth2\Db\AccessTokenMapper');

		/** @var Client $client */
		$client = new Client();
		$client->setIdentifier($this->clientIdentifier1);
		$client->setSecret($this->clientSecret);
		$client->setRedirectUri('https://owncloud.org');
		$client->setName('ownCloud');
		$this->client1 = $this->clientMapper->insert($client);

		$client = new Client();
		$client->setIdentifier($this->clientIdentifier2);
		$client->setSecret($this->clientSecret);
		$client->setRedirectUri('https://www.google.de');
		$client->setName('Google');
		$this->client2 = $this->clientMapper->insert($client);

		/** @var AuthorizationCode $authorizationCode */
		$authorizationCode = new AuthorizationCode();
		$authorizationCode->setCode('kYz7us4yr4QZyUZuMIkVZUf1v2IzvsFZaNXCy3M3amqVGF62AJVJaCfz6FM9pecV');
		$authorizationCode->setClientId($this->client1->getId());
		$authorizationCode->setUserId($this->userId);
		$authorizationCode->setExpires(null);
		$this->authorizationCode = $this->authorizationCodeMapper->insert($authorizationCode);

		$this->controller = new OAuthApiController('oauth2', $request, $this->clientMapper, $this->authorizationCodeMapper, $this->accessTokenMapper);
	}

	public function tearDown() {
		$this->authorizationCodeMapper->delete($this->authorizationCode);
		$this->clientMapper->delete($this->client1);
		$this->clientMapper->delete($this->client2);
	}

	public function testGenerateToken() {
		$_SERVER['PHP_AUTH_USER'] = null;
		$_SERVER['PHP_AUTH_PW'] = null;

		$result = $this->controller->generateToken($this->authorizationCode->getCode());
		$this->assertTrue($result instanceof JSONResponse);
		$json = json_decode($result->render());
		$this->assertNotEmpty($json->message);
		$this->assertEquals('Missing credentials.', $json->message);
		$this->assertEquals(400, $result->getStatus());

		$_SERVER['PHP_AUTH_USER'] = 'test';
		$_SERVER['PHP_AUTH_PW'] = $this->clientSecret;

		$result = $this->controller->generateToken($this->authorizationCode->getCode());
		$this->assertTrue($result instanceof JSONResponse);
		$json = json_decode($result->render());
		$this->assertNotEmpty($json->message);
		$this->assertEquals('Unknown credentials.', $json->message);
		$this->assertEquals(400, $result->getStatus());

		$_SERVER['PHP_AUTH_USER'] = $this->clientIdentifier1;
		$_SERVER['PHP_AUTH_PW'] = 'test';

		$result = $this->controller->generateToken($this->authorizationCode->getCode());
		$this->assertTrue($result instanceof JSONResponse);
		$json = json_decode($result->render());
		$this->assertNotEmpty($json->message);
		$this->assertEquals('Unknown credentials.', $json->message);
		$this->assertEquals(400, $result->getStatus());

		$_SERVER['PHP_AUTH_PW'] = $this->clientSecret;

		$result = $this->controller->generateToken(null);
		$this->assertTrue($result instanceof JSONResponse);
		$json = json_decode($result->render());
		$this->assertNotEmpty($json->message);
		$this->assertEquals('Missing credentials.', $json->message);
		$this->assertEquals(400, $result->getStatus());

		$_SERVER['PHP_AUTH_USER'] = $this->clientIdentifier2;

		$result = $this->controller->generateToken($this->authorizationCode->getCode());
		$this->assertTrue($result instanceof JSONResponse);
		$json = json_decode($result->render());
		$this->assertNotEmpty($json->message);
		$this->assertEquals('Unknown credentials.', $json->message);
		$this->assertEquals(400, $result->getStatus());

		$_SERVER['PHP_AUTH_USER'] = $this->clientIdentifier1;

		$result = $this->controller->generateToken('test');
		$this->assertTrue($result instanceof JSONResponse);
		$json = json_decode($result->render());
		$this->assertNotEmpty($json->message);
		$this->assertEquals('Unknown credentials.', $json->message);
		$this->assertEquals(400, $result->getStatus());

		$result = $this->controller->generateToken($this->authorizationCode->getCode());
		$this->assertTrue($result instanceof JSONResponse);
		$json = json_decode($result->render());
		$this->assertNotEmpty($json->access_token);
		$this->assertEquals(64, strlen($json->access_token));
		$this->assertNotEmpty($json->token_type);
		$this->assertEquals('Bearer', $json->token_type);
		$this->assertNotEmpty($json->user_id);
		$this->assertEquals($this->userId, $json->user_id);
		$this->assertEquals(200, $result->getStatus());

		$accessToken = $this->accessTokenMapper->findByToken($json->access_token);
		$this->accessTokenMapper->delete($accessToken);
	}

}
