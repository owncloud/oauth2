<?php
namespace OCA\oauth2\Migrations;

use Doctrine\DBAL\Schema\Schema;
use OCP\Migration\ISchemaMigration;

/**
 * This adds a unique index for token auth queries
 */
class Version20220312110422 implements ISchemaMigration {
	public function changeSchema(Schema $schema, array $options) {
		$prefix = $options['tablePrefix'];
		$table = $schema->getTable("{$prefix}oauth2_access_tokens");
		if (!$table->hasIndex('oauth2_token')) {
			$table->addUniqueIndex(['token'], 'oauth2_token');
		}
	}
}
