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
use OCA\OAuth2\Db\ClientMapper;
use OCP\AppFramework\Controller;
use OCP\AppFramework\Http\RedirectResponse;
use OCP\AppFramework\Http\TemplateResponse;
use OCP\IRequest;

class SettingsController extends Controller {

    private $clientMapper;

    public function __construct($AppName, IRequest $request, ClientMapper $mapper) {
        parent::__construct($AppName, $request);
        $this->clientMapper = $mapper;
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
        $clients = $this->clientMapper->findAll();
        echo var_dump($clients);
        echo $_POST["name"].$_POST["redirect_uri"];
        return new RedirectResponse('../settings/admin#oauth-2.0');
    }

}
