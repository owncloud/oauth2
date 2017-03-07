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

namespace OCA\OAuth2\Tests\Unit\Controller;

use OCA\OAuth2\AppInfo\Application;
use OCA\OAuth2\Controller\OAuthApiController;
use OCA\OAuth2\Db\AccessToken;
use OCA\OAuth2\Db\AccessTokenMapper;
use OCA\OAuth2\Db\AuthorizationCode;
use OCA\OAuth2\Db\AuthorizationCodeMapper;
use OCA\OAuth2\Db\Client;
use OCA\OAuth2\Db\ClientMapper;
use OCA\OAuth2\Db\RefreshToken;
use OCA\OAuth2\Db\RefreshTokenMapper;
use OCP\AppFramework\Http\JSONResponse;
use PHPUnit_Framework_TestCase;

class OAuthApiControllerTest extends PHPUnit_Framework_TestCase {

	/** @var OAuthApiController $controller */
	private $controller;

	/** @var ClientMapper $clientMapper */
	private $clientMapper;

	/** @var AuthorizationCodeMapper $authorizationCodeMapper */
	private $authorizationCodeMapper;

	/** @var AccessTokenMapper */
	private $accessTokenMapper;

	/** @var RefreshTokenMapper */
	private $refreshTokenMapper;

	/** @var string $userId */
	private $userId = 'john';

	/** @var string $clientIdentifier1 */
	private $clientIdentifier1 = 'NXCy3M3a6FM9pecVyUZuGF62AJVJaCfmkYz7us4yr4QZqVzMIkVZUf1v2IzvsFZa';

	/** @var string $clientIdentifier2 */
	private $clientIdentifier2 = 'JaCfmkYz7MIkXCy3M3a6FM9pecVyUZVZUf1v2IzvsFZaNuGF62AJVus4yr4QZqVz';

	/** @var string $clientSecret */
	private $clientSecret = '9yUZuGF6pecVaCfmIzvsFZakYNXCyr4QZqVzMIky3M3a6FMz7us4VZUf2AJVJ1v2';

	/** @var string $redirectUri */
	private $redirectUri = 'https://owncloud.org';

	/** @var Client $client1 */
	private $client1;

	/** @var Client $client2 */
	private $client2;

	/** @var AuthorizationCode $authorizationCode */
	private $authorizationCode;

	/** @var AccessToken $accessToken */
	private $accessToken;

	/** @var RefreshToken $refreshToken */
	private $refreshToken;

	public function setUp() {
		parent::setUp();

		$request = $this->getMockBuilder('OCP\IRequest')->getMock();

		$app = new Application();
		$container = $app->getContainer();

		$this->clientMapper = $container->query('OCA\OAuth2\Db\ClientMapper');
		$this->clientMapper->deleteAll();
		$this->authorizationCodeMapper = $container->query('OCA\OAuth2\Db\AuthorizationCodeMapper');
		$this->authorizationCodeMapper->deleteAll();
		$this->accessTokenMapper = $container->query('OCA\OAuth2\Db\AccessTokenMapper');
		$this->accessTokenMapper->deleteAll();
		$this->refreshTokenMapper = $container->query('OCA\OAuth2\Db\RefreshTokenMapper');
		$this->refreshTokenMapper->deleteAll();

		/** @var Client $client */
		$client = new Client();
		$client->setIdentifier($this->clientIdentifier1);
		$client->setSecret($this->clientSecret);
		$client->setRedirectUri($this->redirectUri);
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
		$authorizationCode->resetExpires();
		$this->authorizationCode = $this->authorizationCodeMapper->insert($authorizationCode);

		/** @var AccessToken $accessToken */
		$accessToken = new AccessToken();
		$accessToken->setToken('sFz6FM9pecGF62kYz7us43M3amqVZaNQZyUZuMIkAJVJaCfVyr4Uf1v2IzvVZXCy');
		$accessToken->setClientId($this->client1->getId());
		$accessToken->setUserId($this->userId);
		$accessToken->resetExpires();
		$this->accessToken = $this->accessTokenMapper->insert($accessToken);

		/** @var RefreshToken $refreshToken */
		$refreshToken = new RefreshToken();
		$refreshToken->setToken('GF62kYz7us4yr4Uf1v2IzvsFZaNQZyUZuMIkAJVJaCfz6FM9pecVZXCy3M3amqVV');
		$refreshToken->setClientId($this->client1->getId());
		$refreshToken->setUserId($this->userId);
		$this->refreshToken = $this->refreshTokenMapper->insert($refreshToken);

		$this->controller = new OAuthApiController('oauth2', $request, $this->clientMapper, $this->authorizationCodeMapper, $this->accessTokenMapper, $this->refreshTokenMapper);
	}

	public function tearDown() {
		parent::tearDown();

		$this->clientMapper->delete($this->client1);
		$this->clientMapper->delete($this->client2);
		$this->authorizationCodeMapper->delete($this->authorizationCode);
		$this->refreshTokenMapper->delete($this->refreshToken);
	}

	public function testGenerateTokenWithUnknownGrantType() {
		$_SERVER['PHP_AUTH_USER'] = $this->clientIdentifier1;
		$_SERVER['PHP_AUTH_PW'] = $this->clientSecret;

		$result = $this->controller->generateToken('unknown');
		$this->assertTrue($result instanceof JSONResponse);
		$json = json_decode($result->render());
		$this->assertNotEmpty($json->error);
		$this->assertEquals('invalid_grant', $json->error);
		$this->assertEquals(400, $result->getStatus());
	}

	public function testGenerateTokenWithAuthorizationCode() {
		$_SERVER['PHP_AUTH_USER'] = null;
		$_SERVER['PHP_AUTH_PW'] = null;

		$result = $this->controller->generateToken(null, $this->authorizationCode->getCode(), $this->redirectUri);
		$this->assertTrue($result instanceof JSONResponse);
		$json = json_decode($result->render());
		$this->assertNotEmpty($json->error);
		$this->assertEquals('invalid_request', $json->error);
		$this->assertEquals(400, $result->getStatus());

		$result = $this->controller->generateToken('authorization_code', $this->authorizationCode->getCode(), $this->redirectUri);
		$this->assertTrue($result instanceof JSONResponse);
		$json = json_decode($result->render());
		$this->assertNotEmpty($json->error);
		$this->assertEquals('invalid_request', $json->error);
		$this->assertEquals(400, $result->getStatus());

		$_SERVER['PHP_AUTH_USER'] = 'test';
		$_SERVER['PHP_AUTH_PW'] = $this->clientSecret;

		$result = $this->controller->generateToken('authorization_code', $this->authorizationCode->getCode(), $this->redirectUri);
		$this->assertTrue($result instanceof JSONResponse);
		$json = json_decode($result->render());
		$this->assertNotEmpty($json->error);
		$this->assertEquals('invalid_client', $json->error);
		$this->assertEquals(400, $result->getStatus());

		$_SERVER['PHP_AUTH_USER'] = $this->clientIdentifier1;
		$_SERVER['PHP_AUTH_PW'] = 'test';

		$result = $this->controller->generateToken('authorization_code', $this->authorizationCode->getCode(), $this->redirectUri);
		$this->assertTrue($result instanceof JSONResponse);
		$json = json_decode($result->render());
		$this->assertNotEmpty($json->error);
		$this->assertEquals('invalid_client', $json->error);
		$this->assertEquals(400, $result->getStatus());

		$_SERVER['PHP_AUTH_PW'] = $this->clientSecret;

		$result = $this->controller->generateToken('authorization_code', null);
		$this->assertTrue($result instanceof JSONResponse);
		$json = json_decode($result->render());
		$this->assertNotEmpty($json->error);
		$this->assertEquals('invalid_request', $json->error);
		$this->assertEquals(400, $result->getStatus());

		$_SERVER['PHP_AUTH_USER'] = $this->clientIdentifier2;

		$result = $this->controller->generateToken('authorization_code', $this->authorizationCode->getCode(), $this->redirectUri);
		$this->assertTrue($result instanceof JSONResponse);
		$json = json_decode($result->render());
		$this->assertNotEmpty($json->error);
		$this->assertEquals('invalid_grant', $json->error);
		$this->assertEquals(400, $result->getStatus());

		$_SERVER['PHP_AUTH_USER'] = $this->clientIdentifier1;

		$result = $this->controller->generateToken('authorization_code', 'test', $this->redirectUri);
		$this->assertTrue($result instanceof JSONResponse);
		$json = json_decode($result->render());
		$this->assertNotEmpty($json->error);
		$this->assertEquals('invalid_grant', $json->error);
		$this->assertEquals(400, $result->getStatus());

		$result = $this->controller->generateToken('authorization_code', $this->authorizationCode->getCode(), 'http://www.example.org');
		$this->assertTrue($result instanceof JSONResponse);
		$json = json_decode($result->render());
		$this->assertNotEmpty($json->error);
		$this->assertEquals('invalid_grant', $json->error);
		$this->assertEquals(400, $result->getStatus());

		$this->authorizationCode->setExpires(time() - 1);
		$this->authorizationCodeMapper->update($this->authorizationCode);
		$result = $this->controller->generateToken('authorization_code', $this->authorizationCode->getCode(), $this->redirectUri);
		$this->assertTrue($result instanceof JSONResponse);
		$json = json_decode($result->render());
		$this->assertNotEmpty($json->error);
		$this->assertEquals('invalid_grant', $json->error);
		$this->assertEquals(400, $result->getStatus());

		$this->authorizationCode->resetExpires();
		$this->authorizationCodeMapper->update($this->authorizationCode);
		$result = $this->controller->generateToken('authorization_code', $this->authorizationCode->getCode(), $this->redirectUri);
		$this->assertTrue($result instanceof JSONResponse);
		$json = json_decode($result->render());
		$this->assertNotEmpty($json->access_token);
		$this->assertEquals(64, strlen($json->access_token));
		$this->assertNotEmpty($json->token_type);
		$this->assertEquals('Bearer', $json->token_type);
		$this->assertNotEmpty($json->expires_in);
		$this->assertEquals(3600, $json->expires_in);
		$this->assertNotEmpty($json->refresh_token);
		$this->assertEquals(64, strlen($json->refresh_token));
		$this->assertNotEmpty($json->user_id);
		$this->assertEquals($this->userId, $json->user_id);
		$this->assertEquals(200, $result->getStatus());
		$this->assertEquals(0, count($this->authorizationCodeMapper->findAll()));
		$this->assertEquals(1, count($this->accessTokenMapper->findAll()));
		$this->assertEquals(1, count($this->refreshTokenMapper->findAll()));
	}

	public function testGenerateTokenWithRefreshToken() {
		$_SERVER['PHP_AUTH_USER'] = null;
		$_SERVER['PHP_AUTH_PW'] = null;

		$result = $this->controller->generateToken('refresh_token', null, null, $this->refreshToken->getToken());
		$this->assertTrue($result instanceof JSONResponse);
		$json = json_decode($result->render());
		$this->assertNotEmpty($json->error);
		$this->assertEquals('invalid_request', $json->error);
		$this->assertEquals(400, $result->getStatus());

		$_SERVER['PHP_AUTH_USER'] = 'test';
		$_SERVER['PHP_AUTH_PW'] = $this->clientSecret;

		$result = $this->controller->generateToken('refresh_token', null, null, $this->refreshToken->getToken());
		$this->assertTrue($result instanceof JSONResponse);
		$json = json_decode($result->render());
		$this->assertNotEmpty($json->error);
		$this->assertEquals('invalid_client', $json->error);
		$this->assertEquals(400, $result->getStatus());

		$_SERVER['PHP_AUTH_USER'] = $this->clientIdentifier1;
		$_SERVER['PHP_AUTH_PW'] = 'test';

		$result = $this->controller->generateToken('refresh_token', null, null, $this->refreshToken->getToken());
		$this->assertTrue($result instanceof JSONResponse);
		$json = json_decode($result->render());
		$this->assertNotEmpty($json->error);
		$this->assertEquals('invalid_client', $json->error);
		$this->assertEquals(400, $result->getStatus());

		$_SERVER['PHP_AUTH_PW'] = $this->clientSecret;

		$result = $this->controller->generateToken('refresh_token', null, null, null);
		$this->assertTrue($result instanceof JSONResponse);
		$json = json_decode($result->render());
		$this->assertNotEmpty($json->error);
		$this->assertEquals('invalid_request', $json->error);
		$this->assertEquals(400, $result->getStatus());

		$_SERVER['PHP_AUTH_USER'] = $this->clientIdentifier2;

		$result = $this->controller->generateToken('refresh_token', null, null, $this->refreshToken->getToken());
		$this->assertTrue($result instanceof JSONResponse);
		$json = json_decode($result->render());
		$this->assertNotEmpty($json->error);
		$this->assertEquals('invalid_grant', $json->error);
		$this->assertEquals(400, $result->getStatus());

		$_SERVER['PHP_AUTH_USER'] = $this->clientIdentifier1;

		$result = $this->controller->generateToken('refresh_token', null, null, 'test');
		$this->assertTrue($result instanceof JSONResponse);
		$json = json_decode($result->render());
		$this->assertNotEmpty($json->error);
		$this->assertEquals('invalid_grant', $json->error);
		$this->assertEquals(400, $result->getStatus());

		$result = $this->controller->generateToken('refresh_token', null, null, $this->refreshToken->getToken());
		$this->assertTrue($result instanceof JSONResponse);
		$json = json_decode($result->render());
		$this->assertNotEmpty($json->access_token);
		$this->assertEquals(64, strlen($json->access_token));
		$this->assertNotEmpty($json->token_type);
		$this->assertEquals('Bearer', $json->token_type);
		$this->assertNotEmpty($json->expires_in);
		$this->assertEquals(3600, $json->expires_in);
		$this->assertNotEmpty($json->refresh_token);
		$this->assertEquals(64, strlen($json->refresh_token));
		$this->assertNotEmpty($json->user_id);
		$this->assertEquals($this->userId, $json->user_id);
		$this->assertEquals(200, $result->getStatus());
		$this->assertEquals(1, count($this->accessTokenMapper->findAll()));
		$this->assertEquals(1, count($this->refreshTokenMapper->findAll()));
	}

}
