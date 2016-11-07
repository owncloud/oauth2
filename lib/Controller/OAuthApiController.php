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

use OCP\IRequest;
use OCP\AppFramework\ApiController;

class OAuthApiController extends ApiController {

	public function __construct($appName, IRequest $request) {
		parent::__construct($appName, $request);
	}

	/**
	 * Implements the OAuth 2.0 Authorization Response.
	 *
	 * @NoAdminRequired
	 * @NoCSRFRequired
	 * @PublicPage
	 * @CORS
	 */
	public function accessCode() {
		// TODO: implement method
	}

	/**
	 * Implements the OAuth 2.0 Access Token Response.
	 *
	 * Is accessible by the client via the /index.php/apps/oauth2/token
	 *
	 * @NoAdminRequired
	 * @NoCSRFRequired
	 * @PublicPage
	 * @CORS
	 */
	public function token() {
		// TODO: implement method
	}

}
