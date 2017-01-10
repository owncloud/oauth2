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

use InvalidArgumentException;
use OCP\AppFramework\Db\Entity;
use OCP\IDb;
use OCP\AppFramework\Db\Mapper;

class AuthorizationCodeMapper extends Mapper {

    public function __construct(IDb $db) {
        parent::__construct($db, 'oauth2_authorization_codes');
    }

    /**
     * Selects an authorization code by its ID.
     *
     * @param int $id The authorization code's ID.
     *
     * @return Entity The authorization code entity.
     *
     * @throws \OCP\AppFramework\Db\DoesNotExistException if not found.
     * @throws \OCP\AppFramework\Db\MultipleObjectsReturnedException if more
     * than one result.
     */
    public function find($id) {
		if (!is_int($id)) {
			throw new InvalidArgumentException('Argument id must be an int');
		}

        $sql = 'SELECT * FROM `'. $this->tableName . '` WHERE `id` = ?';
        return $this->findEntity($sql, [$id], null, null);
    }

    /**
     * Selects an authorization code by its code.
     *
     * @param string $code The authorization code.
     *
     * @return Entity The authorization code entity.
     *
     * @throws \OCP\AppFramework\Db\DoesNotExistException if not found.
     * @throws \OCP\AppFramework\Db\MultipleObjectsReturnedException if more
     * than one result.
     */
    public function findByCode($code) {
		if (!is_string($code)) {
			throw new InvalidArgumentException('Argument code must be a string');
		}

        $sql = 'SELECT * FROM `'. $this->tableName . '` WHERE `code` = ?';
        return $this->findEntity($sql, [$code], null, null);
    }

    /**
     * Selects all authorization codes.
     *
     * @param int $limit The maximum number of rows.
     * @param int $offset From which row we want to start.
	 *
     * @return array All authorization codes.
     */
    public function findAll($limit = null, $offset = null) {
        $sql = 'SELECT * FROM `' . $this->tableName . '`';
        return $this->findEntities($sql, [], $limit, $offset);
    }

	/**
	 * Deletes all authorization codes for given client and user ID.
	 *
	 * @param int $clientId The client ID.
	 * @param string $userId The user ID.
	 */
	public function deleteByClientUser($clientId, $userId) {
		if (!is_int($clientId) || !is_string($userId)) {
			throw new InvalidArgumentException('Argument client_id must be an int and user_id must be a string');
		}

		$sql = 'DELETE FROM `' . $this->tableName . '` '
			. 'WHERE client_id = ? AND user_id = ?';
		$stmt = $this->execute($sql, [$clientId, $userId], null, null);
		$stmt->closeCursor();
	}

	/**
	 * Deletes all entities from the table
	 */
	public function deleteAll(){
		$sql = 'DELETE FROM `' . $this->tableName . '`';
		$stmt = $this->execute($sql, []);
		$stmt->closeCursor();
	}

}
