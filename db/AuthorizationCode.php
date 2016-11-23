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

namespace OCA\OAuth2\Db;

use \OCP\AppFramework\Db\Entity;

class AuthorizationCode extends Entity {

    protected $clientId;
    protected $userId;
    protected $redirectUri;
    protected $expires;

    public function __construct() {
        $this->addType('id', 'string');
        $this->addType('client_id', 'string');
        $this->addType('user_id', 'string');
        $this->addType('redirect_uri', 'string');
        //$this->addType('expires', 'string');
    }

}
