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

namespace OCA\OAuth2\Controller;

use OCA\OAuth2\Db\AccessToken;
use OCA\OAuth2\Db\AccessTokenMapper;
use OCA\OAuth2\Db\AuthorizationCode;
use OCA\OAuth2\Db\AuthorizationCodeMapper;
use OCA\OAuth2\Db\Client;
use OCA\OAuth2\Db\ClientMapper;
use OCA\OAuth2\Db\RefreshToken;
use OCA\OAuth2\Db\RefreshTokenMapper;
use OCA\OAuth2\Utilities;
use OCP\AppFramework\ApiController;
use OCP\AppFramework\Db\DoesNotExistException;
use OCP\AppFramework\Http;
use OCP\AppFramework\Http\JSONResponse;
use OCP\ILogger;
use OCP\IRequest;

class OAuthApiController extends ApiController {

	/** @var ClientMapper */
	private $clientMapper;

	/** @var AuthorizationCodeMapper */
	private $authorizationCodeMapper;

	/** @var AccessTokenMapper */
	private $accessTokenMapper;

	/** @var RefreshTokenMapper */
	private $refreshTokenMapper;

	/** @var ILogger */
	private $logger;

	/**
	 * OAuthApiController constructor.
	 *
	 * @param string $appName The app's name.
	 * @param IRequest $request The request.
	 * @param ClientMapper $clientMapper The client mapper.
	 * @param AuthorizationCodeMapper $authorizationCodeMapper The authorization code mapper.
	 * @param AccessTokenMapper $accessTokenMapper The access token mapper.
	 * @param RefreshTokenMapper $refreshTokenMapper The refresh token mapper.
	 * @param ILogger $logger The logger.
	 */
	public function __construct($appName, IRequest $request,
								ClientMapper $clientMapper,
								AuthorizationCodeMapper $authorizationCodeMapper,
								AccessTokenMapper $accessTokenMapper,
								RefreshTokenMapper $refreshTokenMapper,
								ILogger $logger) {
		parent::__construct($appName, $request);

		$this->clientMapper = $clientMapper;
		$this->authorizationCodeMapper = $authorizationCodeMapper;
		$this->accessTokenMapper = $accessTokenMapper;
		$this->refreshTokenMapper = $refreshTokenMapper;
		$this->logger = $logger;
	}

	/**
	 * Implements the OAuth 2.0 Access Token Response.
	 *
	 * @param string $grant_type The authorization grant type.
	 * @param string $code The authorization code.
	 * @param string $redirect_uri The redirect URI.
	 * @param string $refresh_token The refresh token.
	 * @return JSONResponse The Access Token or an empty JSON Object.
	 *
	 * @NoAdminRequired
	 * @NoCSRFRequired
	 * @PublicPage
	 * @CORS
	 */
	public function generateToken($grant_type, $code = null,
								  $redirect_uri = null, $refresh_token = null) {
		if (!is_string($grant_type)) {
			return new JSONResponse(['error' => 'invalid_request'], Http::STATUS_BAD_REQUEST);
		}

		if (is_null($_SERVER['PHP_AUTH_USER']) || is_null($_SERVER['PHP_AUTH_PW'])) {
			return new JSONResponse(['error' => 'invalid_request'], Http::STATUS_BAD_REQUEST);
		}

		try {
			/** @var Client $client */
			$client = $this->clientMapper->findByIdentifier($_SERVER['PHP_AUTH_USER']);
		} catch (DoesNotExistException $exception) {
			return new JSONResponse(['error' => 'invalid_client'], Http::STATUS_BAD_REQUEST);
		}

		if (strcmp($client->getSecret(), $_SERVER['PHP_AUTH_PW']) !== 0) {
			return new JSONResponse(['error' => 'invalid_client'], Http::STATUS_BAD_REQUEST);
		}

		switch ($grant_type) {
			case 'authorization_code':
				if (!is_string($code) || !is_string($redirect_uri)) {
					return new JSONResponse(['error' => 'invalid_request'], Http::STATUS_BAD_REQUEST);
				}

				try {
					/** @var AuthorizationCode $authorizationCode */
					$authorizationCode = $this->authorizationCodeMapper->findByCode($code);
				} catch (DoesNotExistException $exception) {
					return new JSONResponse(['error' => 'invalid_grant'], Http::STATUS_BAD_REQUEST);
				}

				if (strcmp($authorizationCode->getClientId(), $client->getId()) !== 0) {
					return new JSONResponse(['error' => 'invalid_grant'], Http::STATUS_BAD_REQUEST);
				}

				if ($authorizationCode->hasExpired()) {
					return new JSONResponse(['error' => 'invalid_grant'], Http::STATUS_BAD_REQUEST);
				}

				if (!Utilities::validateRedirectUri($client->getRedirectUri(), urldecode($redirect_uri), $client->getAllowSubdomains())) {
					return new JSONResponse(['error' => 'invalid_grant'], Http::STATUS_BAD_REQUEST);
				}

				$this->logger->info('An authorization code has been used by the client "' . $client->getName() .'" to request an access token.', ['app' => $this->appName]);

				$userId = $authorizationCode->getUserId();
				break;
			case 'refresh_token':
				if (!is_string($refresh_token)) {
					return new JSONResponse(['error' => 'invalid_request'], Http::STATUS_BAD_REQUEST);
				}

				try {
					/** @var RefreshToken $refreshToken */
					$refreshToken = $this->refreshTokenMapper->findByToken($refresh_token);
				} catch (DoesNotExistException $exception) {
					return new JSONResponse(['error' => 'invalid_grant'], Http::STATUS_BAD_REQUEST);
				}

				if (strcmp($refreshToken->getClientId(), $client->getId()) !== 0) {
					return new JSONResponse(['error' => 'invalid_grant'], Http::STATUS_BAD_REQUEST);
				}

				$this->logger->info('A refresh token has been used by the client "' . $client->getName() .'" to request an access token.', ['app' => $this->appName]);

				$userId = $refreshToken->getUserId();
				break;
			default:
				return new JSONResponse(['error' => 'invalid_grant'], Http::STATUS_BAD_REQUEST);
		}

		$this->authorizationCodeMapper->deleteByClientUser($client->getId(), $userId);
		$this->accessTokenMapper->deleteByClientUser($client->getId(), $userId);
		$this->refreshTokenMapper->deleteByClientUser($client->getId(), $userId);

		$token = Utilities::generateRandom();
		$accessToken = new AccessToken();
		$accessToken->setToken($token);
		$accessToken->setClientId($client->getId());
		$accessToken->setUserId($userId);
		$accessToken->resetExpires();
		$this->accessTokenMapper->insert($accessToken);

		$token = Utilities::generateRandom();
		$refreshToken = new RefreshToken();
		$refreshToken->setToken($token);
		$refreshToken->setClientId($client->getId());
		$refreshToken->setUserId($userId);
		$this->refreshTokenMapper->insert($refreshToken);

		return new JSONResponse(
			[
				'access_token' => $accessToken->getToken(),
				'token_type' => 'Bearer',
				'expires_in' => 3600,
				'refresh_token' => $refreshToken->getToken(),
				'user_id' => $userId
			]
		);
	}

}
