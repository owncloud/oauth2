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

use \OCA\OAuth2\Db\AccessToken;
use \OCA\OAuth2\Db\AccessTokenMapper;
use \OCA\OAuth2\Db\AuthorizationCodeMapper;
use \OCA\OAuth2\Db\ClientMapper;
use \OCA\OAuth2\Utilities;
use \OCP\AppFramework\Db\DoesNotExistException;
use \OCP\AppFramework\Http;
use \OCP\AppFramework\Http\JSONResponse;
use \OCP\IRequest;
use \OCP\AppFramework\ApiController;

class OAuthApiController extends ApiController {

    /** @var ClientMapper */
    private $clientMapper;

	/** @var AuthorizationCodeMapper */
	private $authorizationCodeMapper;

	/** @var AccessTokenMapper */
	private $accessTokenMapper;

    /**
     * OAuthApiController constructor.
     * @param string $appName
     * @param IRequest $request
     * @param ClientMapper $clientMapper
	 * @param AuthorizationCodeMapper $authorizationCodeMapper
	 * @param AccessTokenMapper $accessTokenMapper
     */
	public function __construct($appName, IRequest $request, ClientMapper $clientMapper, AuthorizationCodeMapper $authorizationCodeMapper, AccessTokenMapper $accessTokenMapper) {
		parent::__construct($appName, $request);
        $this->clientMapper = $clientMapper;
		$this->authorizationCodeMapper = $authorizationCodeMapper;
		$this->accessTokenMapper = $accessTokenMapper;
	}

	/**
	 * Implements the OAuth 2.0 Access Token Response.
	 *
	 * Is accessible by the client via the /index.php/apps/oauth2/api/v1/token
	 *
     * @param string $code The authorization code.
	 * @return JSONResponse The Access Token or an empty JSON Object.
	 *
	 * @NoAdminRequired
	 * @NoCSRFRequired
	 * @PublicPage
	 * @CORS
	 */
	public function generateToken($code) {
        if (is_null($code) || is_null($_SERVER['PHP_AUTH_USER'])
            || is_null($_SERVER['PHP_AUTH_PW'])) {
            return new JSONResponse(['message' => 'Missing credentials.'], Http::STATUS_BAD_REQUEST);
        }

        try {
            $client = $this->clientMapper->find($_SERVER['PHP_AUTH_USER']);
        } catch (DoesNotExistException $exception) {
            return new JSONResponse(['message' => 'Unknown credentials.'], Http::STATUS_BAD_REQUEST);
        }

        if (strcmp($client->getSecret(), $_SERVER['PHP_AUTH_PW']) !== 0) {
            return new JSONResponse(['message' => 'Unknown credentials.'], Http::STATUS_BAD_REQUEST);
        }

		try {
			$authorizationCode = $this->authorizationCodeMapper->find($code);
		} catch (DoesNotExistException $exception) {
			return new JSONResponse(['message' => 'Unknown credentials.'], Http::STATUS_BAD_REQUEST);
		}

        if (strcmp($authorizationCode->getClientId(), $client->getId()) !== 0) {
            return new JSONResponse(['message' => 'Unknown credentials.'], Http::STATUS_BAD_REQUEST);
        }

		$token = Utilities::generateRandom();
		$userId = $authorizationCode->getUserId();
		$accessToken = new AccessToken();
		$accessToken->setId($token);
		$accessToken->setClientId($authorizationCode->getClientId());
		$accessToken->setUserId($userId);
		$this->accessTokenMapper->insert($accessToken);

        $this->authorizationCodeMapper->delete($authorizationCode);

        return new JSONResponse(
            [
                'access_token' => $token,
                'token_type' => 'Bearer',
				'user_id' => $userId
            ]
        );
	}

}
