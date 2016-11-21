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

use OCP\AppFramework\Db\Entity;
use \OCP\IDb;
use \OCP\AppFramework\Db\Mapper;

class ClientMapper extends Mapper {

    public function __construct(IDb $db) {
        parent::__construct($db, 'oauth2_clients');
    }

    /**
     * Selects a client by its ID.
     *
     * @param string $id The client's ID.
     *
     * @return Entity The client entity.
     *
     * @throws \OCP\AppFramework\Db\DoesNotExistException if not found.
     * @throws \OCP\AppFramework\Db\MultipleObjectsReturnedException if more
     * than one result.
     */
    public function find($id) {
        $sql = 'SELECT * FROM `*PREFIX*oauth2_clients` WHERE `id` = ?';
        return $this->findEntity($sql, array($id));
    }

    /**
     * Selects all clients.
     *
     * @param int $limit The maximum number of rows.
     * @param int $offset From which row we want to start.
     * @return array All clients.
     */
    public function findAll($limit = null, $offset = null) {
        $sql = 'SELECT * FROM `*PREFIX*oauth2_clients`';
        return $this->findEntities($sql, $limit, $offset);
    }

}
