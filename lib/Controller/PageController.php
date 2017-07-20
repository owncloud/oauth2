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
use OCP\AppFramework\Http\RedirectResponse;
use OCP\AppFramework\Http\TemplateResponse;
use OCP\ILogger;
use OCP\IRequest;
use OCP\IURLGenerator;
use OCP\IUserSession;
use OCP\Util;

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
	/** @var IURLGenerator */
	private $urlGenerator;
	/** @var IUserSession */
	private $userSession;

	/**
	 * PageController constructor.
	 *
	 * @param string $AppName The app's name.
	 * @param IRequest $request The request.
	 * @param ClientMapper $clientMapper The client mapper.
	 * @param AuthorizationCodeMapper $authorizationCodeMapper The authorization code mapper.
	 * @param AccessTokenMapper $accessTokenMapper The access token mapper.
	 * @param RefreshTokenMapper $refreshTokenMapper The refresh token mapper.
	 * @param string $UserId The user ID.
	 * @param ILogger $logger The logger.
	 * @param IURLGenerator $urlGenerator
	 * @param IUserSession $userSession
	 */
	public function __construct($AppName, IRequest $request,
								ClientMapper $clientMapper,
								AuthorizationCodeMapper $authorizationCodeMapper,
								AccessTokenMapper $accessTokenMapper,
								RefreshTokenMapper $refreshTokenMapper,
								$UserId,
								ILogger $logger,
								IURLGenerator $urlGenerator,
								IUserSession $userSession
	) {
		parent::__construct($AppName, $request);

		$this->clientMapper = $clientMapper;
		$this->authorizationCodeMapper = $authorizationCodeMapper;
		$this->accessTokenMapper = $accessTokenMapper;
		$this->refreshTokenMapper = $refreshTokenMapper;
		$this->userId = $UserId;
		$this->logger = $logger;
		$this->urlGenerator = $urlGenerator;
		$this->userSession = $userSession;
	}

	/**
	 * Shows a view for the user to authorize a client.
	 *
	 * @param string $response_type The expected response type.
	 * @param string $client_id The client identifier.
	 * @param string $redirect_uri The redirection URI.
	 * @param string $state The state.
	 * @param string | null $user
	 *
	 * @return TemplateResponse The authorize view or the
	 * authorize-error view with a redirection to the
	 * default page URL.
	 * @NoAdminRequired
	 * @NoCSRFRequired
	 */
	public function authorize($response_type, $client_id, $redirect_uri,
							  $state = null, $user = null) {
		if (!is_string($response_type) || !is_string($client_id)
			|| !is_string($redirect_uri) || (isset($state) && !is_string($state))
		) {
			return new TemplateResponse(
				$this->appName,
				'authorize-error',
				['client_name' => null, 'back_url' => OC_Util::getDefaultPageUrl()], 'guest'
			);
		}

		if ($user !== null && $user !== $this->userId) {
			$logoutUrl = $this->urlGenerator->linkToRouteAbsolute(
				'oauth2.page.logout',[
					'user' => $user,
					'requesttoken' => Util::callRegister(),
					'response_type' => $response_type,
					'client_id' => $client_id,
					'redirect_uri' => $redirect_uri,
					'state' => $state
				]
			);
			return new TemplateResponse(
				$this->appName,
				'switch-user',
				['current_user' => $this->userId, 'requested_user' => $user,
					'logout_url' => $logoutUrl], 'guest'
			);
		}
		try {
			/** @var Client $client */
			$client = $this->clientMapper->findByIdentifier($client_id);
		} catch (DoesNotExistException $exception) {
			return new TemplateResponse(
				$this->appName,
				'authorize-error',
				['client_name' => null, 'back_url' => OC_Util::getDefaultPageUrl()], 'guest'
			);
		}

		if (!Utilities::validateRedirectUri($client->getRedirectUri(), urldecode($redirect_uri), $client->getAllowSubdomains())) {
			return new TemplateResponse(
				$this->appName,
				'authorize-error',
				['client_name' => $client->getName(), 'back_url' => OC_Util::getDefaultPageUrl()], 'guest'
			);
		}

		if (strcmp($response_type, 'code') !== 0) {
			return new TemplateResponse(
				$this->appName,
				'authorize-error',
				['client_name' => $client->getName(), 'back_url' => OC_Util::getDefaultPageUrl()], 'guest'
			);
		}

		return new TemplateResponse($this->appName, 'authorize', ['client_name' => $client->getName()], 'guest');
	}

	/**
	 * Implements the OAuth 2.0 Authorization Response.
	 *
	 * @param string $response_type The expected response type.
	 * @param string $client_id The client identifier.
	 * @param string $redirect_uri The redirection URI.
	 * @param string $state The state.
	 *
	 * @return RedirectResponse Redirection to the given redirect_uri or to the
	 * default page URL.
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

				$this->logger->info('An authorization code has been issued for the client "' . $client->getName() . '".', ['app' => $this->appName]);

				return new RedirectResponse($result);
			default:
				return new RedirectResponse(OC_Util::getDefaultPageUrl());
		}
	}

	/**
	 * Shows a message for successful authorization.
	 *
	 * @return TemplateResponse The authorization-successful view.
	 *
	 * @NoAdminRequired
	 * @NoCSRFRequired
	 */
	public function authorizationSuccessful() {
		return new TemplateResponse($this->appName, 'authorization-successful', [], 'guest');
	}

	/**
	 * @NoAdminRequired
	 *
	 * @param string $user
	 * @param string $response_type
	 * @param string $client_id
	 * @param string $redirect_uri
	 * @param string | null $state
	 * @return RedirectResponse | TemplateResponse
	 */
	public function logout($user, $response_type, $client_id, $redirect_uri, $state = null) {
		if (!is_string($response_type) || !is_string($client_id)
			|| !is_string($redirect_uri) || (isset($state) && !is_string($state))
		) {
			return new TemplateResponse(
				$this->appName,
				'authorize-error',
				['client_name' => null, 'back_url' => OC_Util::getDefaultPageUrl()]
			);
		}
		// logout the current user
		$this->userSession->logout();

		$redirectUrl = $this->urlGenerator->linkToRoute('oauth2.page.authorize',[
			'response_type' => $response_type,
			'client_id' => $client_id,
			'redirect_uri' => $redirect_uri,
			'state' => $state,
			'user' => $user
		]);

		// redirect the browser to the login page and set the redirect_url to the authorize page of oauth2
		return new RedirectResponse($this->urlGenerator->linkToRouteAbsolute('core.login.showLoginForm',
			[
				'user' => $user,
				'redirect_url' => $redirectUrl
			]));
	}
}
