<?php

/**
 * @author Ilja Neumann <ineumann@owncloud.com>
 * @copyright Copyright (c) 2017, ownCloud GmbH
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

namespace OCA\OAuth2\Command;

use Doctrine\DBAL\Exception\UniqueConstraintViolationException;
use OCA\OAuth2\Db\Client;
use OCA\OAuth2\Db\ClientMapper;
use OCA\OAuth2\Utilities;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;


class CreateClient extends \Symfony\Component\Console\Command\Command {

	public function __construct($name) {
		parent::__construct($name);
	}

	protected function configure() {
		$this
			->setName('oauth2:create-client')
			->setDescription('Create a OAuth2 client (admin action)')
			->addArgument('name', InputArgument::REQUIRED, 'Name of the client')
			->addArgument('redirect-uri', InputArgument::REQUIRED, 'Redirect Url')
			->addOption('identifier', 'I', InputOption::VALUE_OPTIONAL, "Random generated, set manually for testing only!")
			->addOption(
				'allow-sub-domains',
				's',
				InputOption::VALUE_OPTIONAL,
				'Allow sub-domain',
				false
			);
	}

	protected function execute(InputInterface $input, OutputInterface $output) {
		$client = new Client();

		if ($input->hasOption('identifier')) {
			$identifier = $input->getOption('identifier');
		} else {
			$identifier = Utilities::generateRandom();
		}


		$client->setIdentifier($identifier);
		$client->setSecret(Utilities::generateRandom());
		$client->setName($input->getArgument('name'));
		$client->setRedirectUri($input->getArgument('redirect-uri'));
		$client->setAllowSubdomains($input->getOption('allow-sub-domains'));

		/** @var ClientMapper $clientMapper */
		$clientMapper = \OC::$server->query(ClientMapper::class);

		try {
			$clientMapper->insert($client);
		} catch (UniqueConstraintViolationException $ex) {
			$output->writeln('<error>A client with this name does already exist.</error>');
			return;
		}

		$clientData = [
			'Name' => $client->getName(),
			'Identifier' => $client->getIdentifier(),
			'Secret' => $client->getSecret(),
			'Redirect Uri' => $client->getRedirectUri(),
			'Allow Sub-Domains' => $client->getAllowSubdomains()
		];

		foreach ($clientData as $key => $value) {
			$output->writeln("$key: $value");
		}
	}
}

