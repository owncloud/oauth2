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

use OCP\AppFramework\Http;
use OCP\AppFramework\Http\JSONResponse;
use OCP\IRequest;
use OCP\AppFramework\ApiController;

class OAuthApiController extends ApiController {

	public function __construct($appName, IRequest $request) {
		parent::__construct($appName, $request);
	}

	/**
	 * Implements the OAuth 2.0 Access Token Response.
	 *
	 * Is accessible by the client via the /index.php/apps/oauth2/token
	 *
	 * @return JSONResponse The Access Token or an empty JSON Object.
	 *
	 * @NoAdminRequired
	 * @NoCSRFRequired
	 * @PublicPage
	 * @CORS
	 */
	public function generateToken($access_code) {
		if ($access_code === '123456789'
			&& $_SERVER['PHP_AUTH_USER'] === 'lw'
			&& $_SERVER['PHP_AUTH_PW'] === 'secret') {
			return new JSONResponse(
				[
					'access_token' => '2YotnFZFEjr1zCsicMWpAA',
					'token_type' => 'Bearer'
				]
			);
		}

		return new JSONResponse(['message' => 'Unknown credentials.'], Http::STATUS_BAD_REQUEST);
	}

}
