<?php
namespace OCA\oauth2\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\DBAL\Types\Type;
use OCP\Migration\ISchemaMigration;

class Version20181130110158 implements ISchemaMigration {
	public function changeSchema(Schema $schema, array $options) {
		$prefix = $options['tablePrefix'];
		$table = $schema->getTable("{$prefix}oauth2_refresh_tokens");
		if (!$table->hasColumn('expires')) {
			$table->addColumn('expires', Type::INTEGER, ['notNull' => false, 'unsigned' => true]);
		}
	}
}
