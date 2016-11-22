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
	 * Shows a view for the user to authorize a client.
	 *
	 * Is accessible by the client via /index.php/apps/oauth2/authorize
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
	public function authorize($response_type, $client_id, $redirect_uri, $state) {
		if (is_null($response_type) || is_null($client_id)
			|| is_null($redirect_uri)) {
			return new RedirectResponse('../../');
		}
		if (strcmp($response_type, 'code') !== 0
			|| strcmp($client_id, 'lw') !== 0
			|| $redirect_uri !== urldecode('https://www.google.de')) {
			return new RedirectResponse('../../');
		}

		return new TemplateResponse('oauth2', 'authorize', ['client_id' => $client_id]);
	}

	/**
	 * Implements the OAuth 2.0 Authorization Response.
     *
     * @param string $response_type
     * @param string $client_id
     * @param string $redirect_uri
     * @param string $state
     *
     * @return RedirectResponse|JSONResponse Redirection to the given
     * redirect_uri or a JSON with an error message.
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
