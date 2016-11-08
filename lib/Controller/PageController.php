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
use OCP\AppFramework\Http\TemplateResponse;
use OCP\AppFramework\Http\RedirectResponse;
use OCP\AppFramework\Http\DataResponse;
use OCP\AppFramework\Http\JSONResponse;
use OCP\AppFramework\Controller;
use OCP\AppFramework\Http;

class PageController extends Controller {


	private $userId;

	public function __construct($AppName, IRequest $request, $UserId){
		parent::__construct($AppName, $request);
		$this->userId = $UserId;
	}

	/**
	 * CAUTION: the @Stuff turns off security checks; for this page no admin is
	 *          required and no CSRF check. If you don't know what CSRF is, read
	 *          it up in the docs or you might create a security hole. This is
	 *          basically the only required method to add this exemption, don't
	 *          add it to any other method if you don't exactly know what it does
	 *
	 * @NoAdminRequired
	 * @NoCSRFRequired
	 */
	public function index() {
		$params = ['user' => $this->userId];
		return new TemplateResponse('oauth2', 'main', $params);  // templates/main.php
	}

	/**
	 * Simply method that posts back the payload of the request
	 * @NoAdminRequired
	 */
	public function doEcho($echo) {
		return new DataResponse(['echo' => $echo]);
	}

	/**
	 * Shows a view for the user to authorize a client.
	 *
	 * Is accessible by the client via /index.php/apps/oauth2/authorize
	 *
	 * @NoAdminRequired
	 * @NoCSRFRequired
	 */
	public function authorize($response_type, $client_id, $redirect_uri, $state) {
		if (is_null($response_type) || is_null($client_id)
			|| is_null($redirect_uri)) {
			return new RedirectResponse("../../");
		}
		if (strcmp($response_type, 'code') !== 0
			|| strcmp($client_id, 'lw') !== 0
			|| $redirect_uri !== urldecode('https://www.google.de')) {
			return new RedirectResponse("../../");
		}

		return new TemplateResponse('oauth2', 'authorize', ['client_id' => $client_id]);
	}

	/**
	 * Implements the OAuth 2.0 Authorization Response.
	 *
	 * @NoAdminRequired
	 * @NoCSRFRequired
	 */
	public function generateAccessCode($response_type, $client_id, $redirect_uri, $state) {
		switch ($response_type) {
			case 'code':
				if (strcmp($client_id, 'lw') === 0 && strcmp(urldecode($redirect_uri), 'https://www.google.de') === 0) {
					$result = urldecode($redirect_uri);
					$result = $result. '?code=' . '123456789';
					if (!is_null($state)) {
						$result = $result. '&state=' . $state;
					}
					return new RedirectResponse($result);
				}
				break;
		}

		return new JSONResponse(['message' => 'Unknown credentials.'], Http::STATUS_BAD_REQUEST);
	}

}
