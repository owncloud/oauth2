<?php
/**
 * ownCloud - oauth2
 *
 * This file is licensed under the Affero General Public License version 3 or
 * later. See the COPYING file.
 *
 * @author Jonathan Neugebauer
 * @copyright Jonathan Neugebauer 2016
 */

namespace OCA\OAuth2\Controller;

use OCA\OAuth2\Db\ClientMapper;
use OCP\AppFramework\Db\DoesNotExistException;
use OCP\AppFramework\Http;
use OCP\AppFramework\Http\JSONResponse;
use OCP\IRequest;
use OCP\AppFramework\ApiController;

class OAuthApiController extends ApiController {

    /** @var ClientMapper */
    private $clientMapper;

    /**
     * OAuthApiController constructor.
     * @param string $appName
     * @param IRequest $request
     * @param ClientMapper $mapper
     */
	public function __construct($appName, IRequest $request, ClientMapper $mapper) {
		parent::__construct($appName, $request);
        $this->clientMapper = $mapper;
	}

	/**
	 * Implements the OAuth 2.0 Access Token Response.
	 *
	 * Is accessible by the client via the /index.php/apps/oauth2/token
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

        if ($code !== '123456789') {
            return new JSONResponse(['message' => 'Unknown credentials.'], Http::STATUS_BAD_REQUEST);
        }

        return new JSONResponse(
            [
                'access_token' => '2YotnFZFEjr1zCsicMWpAA',
                'token_type' => 'Bearer'
            ]
        );
	}

}
