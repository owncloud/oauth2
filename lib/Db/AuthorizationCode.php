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

namespace OCA\OAuth2\Db;

use OCP\AppFramework\Db\Entity;

/**
 * Class AuthorizationCode
 *
 * @method int getCode()
 * @method void setCode(int $code)
 * @method int getClientId()
 * @method void setClientId(int $clientId)
 * @method string getUserId()
 * @method void setUserId(string $userId)
 */
class AuthorizationCode extends Entity {

    protected $code;
    protected $clientId;
    protected $userId;
    protected $expires;

    public function __construct() {
        $this->addType('id', 'int');
        $this->addType('code', 'int');
        $this->addType('client_id', 'int');
        $this->addType('user_id', 'string');
		// TODO: set type for $expires
    }

}
