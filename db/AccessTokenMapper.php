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

class AccessTokenMapper extends Mapper {

    public function __construct(IDb $db) {
        parent::__construct($db, 'oauth2_access_tokens');
    }

    /**
     * Selects an access token by its ID.
     *
     * @param string $id The access token's ID.
     *
     * @return Entity The access token entity.
     *
     * @throws \OCP\AppFramework\Db\DoesNotExistException if not found.
     * @throws \OCP\AppFramework\Db\MultipleObjectsReturnedException if more
     * than one result.
     */
    public function find($id) {
        $sql = 'SELECT * FROM `'. $this->tableName . '` WHERE `id` = ?';
        return $this->findEntity($sql, array($id), null, null);
    }

    /**
     * Selects all access tokens.
     *
     * @param int $limit The maximum number of rows.
     * @param int $offset From which row we want to start.
     * @return array All access tokens.
     */
    public function findAll($limit = null, $offset = null) {
        $sql = 'SELECT * FROM `' . $this->tableName . '`';
        return $this->findEntities($sql, [], $limit, $offset);
    }

}
