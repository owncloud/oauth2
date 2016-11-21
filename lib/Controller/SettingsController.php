<?php
/**
 * ownCloud - oauth2
 *
 * This file is licensed under the Affero General Public License version 3 or
 * later. See the COPYING file.
 *
 * @author Lukas Biermann
 * @copyright Lukas Biermann 2016
 */

namespace OCA\OAuth2\Controller;
use OCA\OAuth2\Db\Client;
use OCA\OAuth2\Db\ClientMapper;
use OCP\AppFramework\Controller;
use OCP\AppFramework\Http\RedirectResponse;
use OCP\IRequest;

class SettingsController extends Controller {

    private $clientMapper;
    private $userId;

    public function __construct($AppName, IRequest $request, ClientMapper $mapper, $UserId) {
        parent::__construct($AppName, $request);
        $this->clientMapper = $mapper;
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
        $client = new Client();
        $client->setClientId('abc');
        $client->setName($_POST['name']);
        $client->setClientSecret('topSecret');
        $client->setRedirectUri($_POST['redirect_uri']);
        $client->setGrantTypes('access_code');
        $client->setScope('files');
        $client->setUserId($this->userId);

        $this->clientMapper->insert($client);

        return new RedirectResponse('../../settings/admin#oauth-2.0');
    }

}
