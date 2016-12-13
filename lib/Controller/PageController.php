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

use OCA\OAuth2\Db\AuthorizationCode;
use OCA\OAuth2\Db\AuthorizationCodeMapper;
use OCA\OAuth2\Db\ClientMapper;
use OCA\OAuth2\Utilities;
use OCP\AppFramework\App;
use OCP\AppFramework\Db\DoesNotExistException;
use OCP\IRequest;
use OCP\AppFramework\Http\TemplateResponse;
use OCP\AppFramework\Http\RedirectResponse;
use OCP\AppFramework\Http\JSONResponse;
use OCP\AppFramework\Controller;
use OCP\AppFramework\Http;

class PageController extends Controller {

    /** @var ClientMapper */
    private $clientMapper;

	/** @var AuthorizationCodeMapper */
	private $authorizationCodeMapper;

    /** @var string */
    private $userId;

    /**
     * PageController constructor.
     * @param string $AppName
     * @param IRequest $request
     * @param ClientMapper $clientMapper
	 * @param AuthorizationCodeMapper $authorizationCodeMapper
     * @param string $UserId
     */
	public function __construct($AppName, IRequest $request, ClientMapper $clientMapper, AuthorizationCodeMapper $authorizationCodeMapper, $UserId){
		parent::__construct($AppName, $request);

        $app = new App('oauth2');
        $container = $app->getContainer();

        $this->clientMapper = $clientMapper;
		$this->authorizationCodeMapper = $authorizationCodeMapper;
        $this->userId = $UserId;
	}

	/**
	 * Shows a view for the user to authorize a client.
	 *
	 * Is accessible by the client via /index.php/apps/oauth2/authorize
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
		if (is_null($response_type) || is_null($client_id)
			|| is_null($redirect_uri)) {
			return new RedirectResponse('../../');
		}

		try {
            $client = $this->clientMapper->findByIdentifier($client_id);
        } catch (DoesNotExistException $exception) {
            return new RedirectResponse('../../');
        }

        if (is_null($client)) {
            return new RedirectResponse('../../');
        }
        if (strcmp($client->getRedirectUri(), urldecode($redirect_uri)) !== 0) {
            return new RedirectResponse('../../');
        }
		if (strcmp($response_type, 'code') !== 0) {
			return new RedirectResponse('../../');
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
        if (is_null($response_type) || is_null($client_id)
            || is_null($redirect_uri)) {
            return new RedirectResponse('../../');
        }

		switch ($response_type) {
			case 'code':
                try {
                    $client = $this->clientMapper->findByIdentifier($client_id);
                } catch (DoesNotExistException $exception) {
                    return new RedirectResponse('../../');
                }

                if (is_null($client)) {
                    return new RedirectResponse('../../');
                }
                if (strcmp($client->getRedirectUri(), urldecode($redirect_uri)) !== 0) {
                    return new RedirectResponse('../../');
                }
                if (strcmp($response_type, 'code') !== 0) {
                    return new RedirectResponse('../../');
                }

				$code = Utilities::generateRandom();
				$authorizationCode = new AuthorizationCode();
				$authorizationCode->setIdentifier($code);
				$authorizationCode->setClientId($client->getId());
				$authorizationCode->setUserId($this->userId);
				$this->authorizationCodeMapper->insert($authorizationCode);

                $result = urldecode($redirect_uri);
                $result = $result. '?code=' . $code;
                if (!is_null($state)) {
                    $result = $result. '&state=' . $state;
                }
                return new RedirectResponse($result);
				break;
		}

		return new JSONResponse(['message' => 'Unknown credentials.'], Http::STATUS_BAD_REQUEST);
	}

}
