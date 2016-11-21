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

class Client extends Entity {

    protected $clientId;
    protected $name;
    protected $clientSecret;
    protected $redirectUri;
    protected $grantTypes;
    protected $scope;
    protected $userId;

    public function __construct() {
        $this->addType('client_id', 'string');
        $this->addType('name', 'string');
        $this->addType('client_secret', 'string');
        $this->addType('redirect_uri', 'string');
        $this->addType('grant_types', 'string');
        $this->addType('scope', 'string');
        $this->addType('user_id', 'string');
    }

}
