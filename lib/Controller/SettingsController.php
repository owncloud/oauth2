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
use OCA\OAuth2\Utilities;
use OCP\AppFramework\Controller;
use OCP\AppFramework\Http\RedirectResponse;
use OCP\IRequest;
use OCP\Security\ISecureRandom;

class SettingsController extends Controller {

    /** @var ClientMapper */
    private $clientMapper;

    /** @var string */
    private $userId;

    /**
     * SettingsController constructor.
     *
     * @param string $AppName
     * @param IRequest $request
     * @param ISecureRandom $secureRandom
     * @param ClientMapper $mapper
     * @param string $UserId
     */
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
        if (filter_var($_POST['redirect_uri'], FILTER_VALIDATE_URL) === false) {
            return new RedirectResponse('../../settings/admin#oauth-2.0');
        }

        $client = new Client();
        $client->setId(Utilities::generateRandom());
        $client->setSecret(Utilities::generateRandom());
        $client->setRedirectUri(trim($_POST['redirect_uri']));
        $client->setUserId($this->userId);
        $client->setName(trim($_POST['name']));

        $this->clientMapper->insert($client);

        return new RedirectResponse('../../settings/admin#oauth-2.0');
    }

    /**
     * Deletes a client.
     *
     * @return RedirectResponse Redirection to the settings page.
     *
     * @NoCSRFRequired
     *
     */
    public function deleteClient($id) {
        $client = $this->clientMapper->find($id);
        $this->clientMapper->delete($client);

        return new RedirectResponse('../../../../settings/admin#oauth-2.0');
    }

}
