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

use OC_Util;
use OCA\OAuth2\Db\AccessTokenMapper;
use OCA\OAuth2\Db\AuthorizationCode;
use OCA\OAuth2\Db\AuthorizationCodeMapper;
use OCA\OAuth2\Db\Client;
use OCA\OAuth2\Db\ClientMapper;
use OCA\OAuth2\Db\RefreshTokenMapper;
use OCA\OAuth2\Utilities;
use OCP\AppFramework\Controller;
use OCP\AppFramework\Db\DoesNotExistException;
use OCP\AppFramework\Http\JSONResponse;
use OCP\AppFramework\Http\RedirectResponse;
use OCP\AppFramework\Http\TemplateResponse;
use OCP\ILogger;
use OCP\IRequest;

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

	/** @var ILogger */
	private $logger;

	/**
	 * PageController constructor.
	 *
	 * @param string $appName The apps's name.
	 * @param IRequest $request The request.
	 * @param ClientMapper $clientMapper The client mapper.
	 * @param AuthorizationCodeMapper $authorizationCodeMapper The authorization code mapper.
	 * @param AccessTokenMapper $accessTokenMapper The access token mapper.
	 * @param RefreshTokenMapper $refreshTokenMapper The refresh token mapper.
	 * @param string $UserId The user ID.
	 * @param ILogger $logger The logger.
	 */
	public function __construct($appName, IRequest $request,
								ClientMapper $clientMapper,
								AuthorizationCodeMapper $authorizationCodeMapper,
								AccessTokenMapper $accessTokenMapper,
								RefreshTokenMapper $refreshTokenMapper,
								$UserId,
								ILogger $logger) {
		parent::__construct($appName, $request);

		$this->clientMapper = $clientMapper;
		$this->authorizationCodeMapper = $authorizationCodeMapper;
		$this->accessTokenMapper = $accessTokenMapper;
		$this->refreshTokenMapper = $refreshTokenMapper;
		$this->userId = $UserId;
		$this->logger = $logger;
	}

	/**
	 * Shows a view for the user to authorize a client.
	 *
	 * @param string $response_type The expected response type.
	 * @param string $client_id The client identifier.
	 * @param string $redirect_uri The redirect URI.
	 * @param string $state The state.
	 *
	 * @return TemplateResponse|RedirectResponse The authorize view or a
	 * redirection to the ownCloud main page.
	 *
	 * @NoAdminRequired
	 * @NoCSRFRequired
	 */
	public function authorize($response_type, $client_id, $redirect_uri,
							  $state = null) {
		if (!is_string($response_type) || !is_string($client_id)
			|| !is_string($redirect_uri) || (isset($state) && !is_string($state))
		) {
			return new RedirectResponse(OC_Util::getDefaultPageUrl());
		}

		try {
			/** @var Client $client */
			$client = $this->clientMapper->findByIdentifier($client_id);
		} catch (DoesNotExistException $exception) {
			return new RedirectResponse(OC_Util::getDefaultPageUrl());
		}

		if (!Utilities::validateRedirectUri($client->getRedirectUri(), urldecode($redirect_uri), $client->getAllowSubdomains())) {
			return new RedirectResponse(OC_Util::getDefaultPageUrl());
		}

		if (strcmp($response_type, 'code') !== 0) {
			return new RedirectResponse(OC_Util::getDefaultPageUrl());
		}

		return new TemplateResponse($this->appName, 'authorize', ['client_name' => $client->getName()]);
	}

	/**
	 * Implements the OAuth 2.0 Authorization Response.
	 *
	 * @param string $response_type The expected response type.
	 * @param string $client_id The client identifier.
	 * @param string $redirect_uri The redirect URI.
	 * @param string $state The state.
	 *
	 * @return RedirectResponse|JSONResponse Redirection to the given
	 * redirect_uri or a JSON with an error message.
	 *
	 * @NoAdminRequired
	 * @NoCSRFRequired
	 */
	public function generateAuthorizationCode($response_type, $client_id, $redirect_uri, $state = null) {
		if (!is_string($response_type) || !is_string($client_id)
			|| !is_string($redirect_uri) || (isset($state) && !is_string($state))
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

				if (!Utilities::validateRedirectUri($client->getRedirectUri(), urldecode($redirect_uri), $client->getAllowSubdomains())) {
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

				$this->logger->info('An authorization code has been issued for the client "' . $client->getName() .'".', ['app' => $this->appName]);

				return new RedirectResponse($result);
			default:
				return new RedirectResponse(OC_Util::getDefaultPageUrl());
		}
	}

}
