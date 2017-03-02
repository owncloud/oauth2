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

use OCA\OAuth2\Db\AccessTokenMapper;
use OCA\OAuth2\Db\AuthorizationCodeMapper;
use OCA\OAuth2\Db\Client;
use OCA\OAuth2\Db\ClientMapper;
use OCA\OAuth2\Db\RefreshTokenMapper;
use OCA\OAuth2\Utilities;
use OCP\AppFramework\Controller;
use OCP\AppFramework\Http\RedirectResponse;
use OCP\IRequest;

class SettingsController extends Controller {

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
     * SettingsController constructor.
     *
     * @param string $AppName
     * @param IRequest $request
     * @param ClientMapper $clientMapper
	 * @param AuthorizationCodeMapper $authorizationCodeMapper
	 * @param AccessTokenMapper $accessTokenMapper
	 * @param RefreshTokenMapper $refreshTokenMapper
     * @param string $UserId
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
     * Adds a client.
     *
     * @return RedirectResponse Redirection to the settings page.
     *
     * @NoCSRFRequired
     *
     */
    public function addClient() {
		if (!isset($_POST['redirect_uri']) || !isset($_POST['name'])
			|| filter_var($_POST['redirect_uri'], FILTER_VALIDATE_URL) === false) {
            return new RedirectResponse('../../settings/admin?sectionid=additional#oauth2');
        }

        $client = new Client();
        $client->setIdentifier(Utilities::generateRandom());
        $client->setSecret(Utilities::generateRandom());
        $client->setRedirectUri(trim($_POST['redirect_uri']));
        $client->setName(trim($_POST['name']));

        if (isset($_POST['allow_subdomains'])) {
        	$client->setAllowSubdomains(true);
		} else {
        	$client->setAllowSubdomains(false);
		}

        $this->clientMapper->insert($client);

        return new RedirectResponse('../../settings/admin?sectionid=additional#oauth2');
    }

    /**
     * Deletes a client.
	 *
	 * @param int $id The client identifier.
     *
     * @return RedirectResponse Redirection to the settings page.
     *
     * @NoCSRFRequired
     *
     */
    public function deleteClient($id) {
		if (!is_int($id)) {
			return new RedirectResponse('../../../../settings/admin?sectionid=additional#oauth2');
		}

        $client = $this->clientMapper->find($id);
        $this->clientMapper->delete($client);

        $this->authorizationCodeMapper->deleteByClient($id);
        $this->accessTokenMapper->deleteByClient($id);
        $this->refreshTokenMapper->deleteByClient($id);

        return new RedirectResponse('../../../../settings/admin?sectionid=additional#oauth2');
    }

	/**
	 * Revokes the authorization for a client.
	 *
	 * @param int $id The client identifier.
	 * @param string $user_id The ID of the user logged in.
	 *
	 * @return RedirectResponse Redirection to the settings page.
	 *
	 * @NoCSRFRequired
	 *
	 */
	public function revokeAuthorization($id, $user_id) {
		if (!is_int($id) || !is_string($user_id)) {
			return new RedirectResponse('../../../../settings/personal?sectionid=additional#oauth2');
		}

		$this->authorizationCodeMapper->deleteByClientUser($id, $user_id);
		$this->accessTokenMapper->deleteByClientUser($id, $user_id);
		$this->refreshTokenMapper->deleteByClientUser($id, $user_id);

		return new RedirectResponse('../../../../settings/personal?sectionid=additional#oauth2');
	}

}
