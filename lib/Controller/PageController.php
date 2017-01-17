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

namespace OCA\OAuth2\Controller;

use InvalidArgumentException;
use OC_Util;
use OCA\OAuth2\Db\AccessTokenMapper;
use OCA\OAuth2\Db\AuthorizationCode;
use OCA\OAuth2\Db\AuthorizationCodeMapper;
use OCA\OAuth2\Db\Client;
use OCA\OAuth2\Db\ClientMapper;
use OCA\OAuth2\Db\RefreshTokenMapper;
use OCA\OAuth2\Utilities;
use OCP\AppFramework\Db\DoesNotExistException;
use OCP\IRequest;
use OCP\AppFramework\Http\TemplateResponse;
use OCP\AppFramework\Http\RedirectResponse;
use OCP\AppFramework\Http\JSONResponse;
use OCP\AppFramework\Controller;

class PageController extends Controller {

	/** @var ClientMapper */
	private $clientMapper;

	/** @var AuthorizationCodeMapper */
	private $authorizationCodeMapper;

	/** @var AccessTokenMapper */
	private $accessTokenMapper;

	/** @var RefreshTokenMapper */
	private $refreshTokenMapper;

	/** @var string */
	private $userId;

	/**
	 * PageController constructor.
	 *
	 * @param string $AppName The name of the app.
	 * @param IRequest $request The request.
	 * @param ClientMapper $clientMapper The client mapper.
	 * @param AuthorizationCodeMapper $authorizationCodeMapper The authorization code mapper.
	 * @param AccessTokenMapper $accessTokenMapper The access token mapper.
	 * @param RefreshTokenMapper $refreshTokenMapper The refresh token mapper.
	 * @param string $UserId The user ID.
	 */
	public function __construct($AppName, IRequest $request, ClientMapper $clientMapper, AuthorizationCodeMapper $authorizationCodeMapper, AccessTokenMapper $accessTokenMapper, RefreshTokenMapper $refreshTokenMapper, $UserId) {
		parent::__construct($AppName, $request);

		$this->clientMapper = $clientMapper;
		$this->authorizationCodeMapper = $authorizationCodeMapper;
		$this->accessTokenMapper = $accessTokenMapper;
		$this->refreshTokenMapper = $refreshTokenMapper;
		$this->userId = $UserId;
	}

	/**
	 * Shows a view for the user to authorize a client.
	 *
	 * @param string $response_type The expected response type.
	 * @param string $client_id The client identifier.
	 * @param string $redirect_uri The redirect URI.
	 * @param string $state The state.
	 * @param string $scope The scope.
	 *
	 * @return TemplateResponse|RedirectResponse The authorize view or a
	 * redirection to the ownCloud main page.
	 *
	 * @NoAdminRequired
	 * @NoCSRFRequired
	 */
	public function authorize($response_type, $client_id, $redirect_uri, $state = null, $scope = null) {
		if (!is_string($response_type) || !is_string($client_id)
			|| !is_string($redirect_uri) || (isset($state) && !is_string($state))
			|| (isset($scope) && !is_string($scope))
		) {
			return new RedirectResponse(OC_Util::getDefaultPageUrl());
		}

		try {
			/** @var Client $client */
			$client = $this->clientMapper->findByIdentifier($client_id);
		} catch (DoesNotExistException $exception) {
			return new RedirectResponse(OC_Util::getDefaultPageUrl());
		}

		if (!$this->validateRedirectUri($client->getRedirectUri(), urldecode($redirect_uri), $client->getAllowSubdomains())) {
			return new RedirectResponse(OC_Util::getDefaultPageUrl());
		}

		if (strcmp($response_type, 'code') !== 0) {
			return new RedirectResponse(OC_Util::getDefaultPageUrl());
		}

		return new TemplateResponse('oauth2', 'authorize', ['client_name' => $client->getName()]);
	}

	/**
	 * Implements the OAuth 2.0 Authorization Response.
	 *
	 * @param string $response_type The expected response type.
	 * @param string $client_id The client identifier.
	 * @param string $redirect_uri The redirect URI.
	 * @param string $state The state.
	 * @param string $scope The scope.
	 *
	 * @return RedirectResponse|JSONResponse Redirection to the given
	 * redirect_uri or a JSON with an error message.
	 *
	 * @NoAdminRequired
	 * @NoCSRFRequired
	 */
	public function generateAuthorizationCode($response_type, $client_id, $redirect_uri, $state = null, $scope = null) {
		if (!is_string($response_type) || !is_string($client_id)
			|| !is_string($redirect_uri) || (isset($state) && !is_string($state))
			|| (isset($scope) && !is_string($scope))
		) {
			return new RedirectResponse(OC_Util::getDefaultPageUrl());
		}

		switch ($response_type) {
			case 'code':
				try {
					/** @var Client $client */
					$client = $this->clientMapper->findByIdentifier($client_id);
				} catch (DoesNotExistException $exception) {
					return new RedirectResponse(OC_Util::getDefaultPageUrl());
				}

				if (!$this->validateRedirectUri($client->getRedirectUri(), urldecode($redirect_uri), $client->getAllowSubdomains())) {
					return new RedirectResponse(OC_Util::getDefaultPageUrl());
				}

				$this->authorizationCodeMapper->deleteByClientUser($client->getId(), $this->userId);
				$this->accessTokenMapper->deleteByClientUser($client->getId(), $this->userId);
				$this->refreshTokenMapper->deleteByClientUser($client->getId(), $this->userId);

				$code = Utilities::generateRandom();
				$authorizationCode = new AuthorizationCode();
				$authorizationCode->setCode($code);
				$authorizationCode->setClientId($client->getId());
				$authorizationCode->setUserId($this->userId);
				$authorizationCode->resetExpires();
				$this->authorizationCodeMapper->insert($authorizationCode);

				$result = urldecode($redirect_uri);
				$result = $result . '?code=' . $code;
				if (!is_null($state)) {
					$result = $result . '&state=' . urlencode($state);
				}
				return new RedirectResponse($result);
				break;
			default:
				return new RedirectResponse(OC_Util::getDefaultPageUrl());
		}
	}

	/**
	 * Validates a redirect URI.
	 *
	 * @param $expected String The expected redirect URI.
	 * @param $actual String The actual redirect URI.
	 * @param $allowSubdomains boolean Whether to allow subdomains.
	 *
	 * @return True if the redirect URI is valid, false otherwise.
	 */
	private function validateRedirectUri($expected, $actual, $allowSubdomains) {
		if (strcmp(parse_url($expected, PHP_URL_SCHEME), parse_url($actual, PHP_URL_SCHEME)) !== 0) {
			return false;
		}

		$expectedHost = parse_url($expected, PHP_URL_HOST);
		$actualHost = parse_url($actual, PHP_URL_HOST);

		if ($allowSubdomains) {
			if (strcmp($expectedHost, $actualHost) !== 0
				&& strcmp($expectedHost, str_replace(explode('.', $actualHost)[0] . '.', '', $actualHost)) !== 0
			) {
				return false;
			}
		} else {
			if (strcmp($expectedHost, $actualHost) !== 0) {
				return false;
			}
		}

		if (strcmp(parse_url($expected, PHP_URL_PORT), parse_url($actual, PHP_URL_PORT)) !== 0) {
			return false;
		}

		if (strcmp(parse_url($expected, PHP_URL_PATH), parse_url($actual, PHP_URL_PATH)) !== 0) {
			return false;
		}

		if (strcmp(parse_url($expected, PHP_URL_QUERY), parse_url($actual, PHP_URL_QUERY)) !== 0) {
			return false;
		}

		return true;
	}

}
