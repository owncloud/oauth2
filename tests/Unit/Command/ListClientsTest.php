<?php
/**
 * @author Jan Ackermann <jackermann@owncloud.com>
 * @author Jannik Stehle <jestehle@owncloud.com>
 *
 * @copyright Copyright (c) 2021, ownCloud GmbH
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
 *
 */

namespace OCA\OAuth2\Tests\Unit\Command;

use OCA\OAuth2\Commands\ListClients;
use OCA\OAuth2\Db\Client;
use OCA\OAuth2\Db\ClientMapper;
use Symfony\Component\Console\Tester\CommandTester;
use Test\TestCase;

/**
 * Class ListUsersTest
 */
class ListClientsTest extends TestCase {
	/** @var CommandTester */
	private $commandTester;
	/** @var ClientMapper|\PHPUnit\Framework\MockObject\MockObject */
	private $clientMapper;

	protected function setUp(): void {
		parent::setUp();

		$this->clientMapper = $this->createMock(ClientMapper::class);
		$command = new ListClients($this->clientMapper);
		$this->commandTester = new CommandTester($command);
	}

	public function testCommandInput() {
		$clientData = [
			'id' => 1,
			'name' => 'iOS',
			'redirectUri' => 'oc://ios.owncloud.com',
			'identifier' => 'mxd5OQDk6es5LzOzRvidJNfXLUZS2oN3oUFeXPP8LpPrhx3UroJFduGEYIBOxkY1',
			'secret' => 'KFeFWWEZO9TkisIQzR3fo7hfiMXlOpaqP8CFuTbSHzV1TUuGECglPxpiVKJfOXIx'
		];

		$clientMock = $this->getMockBuilder(Client::class)
			->addMethods([
				'getId',
				'getName',
				'getRedirectUri',
				'getIdentifier',
				'getSecret',
				'getAllowSubdomains',
				'getTrusted'
			])
			->getMock();

		$clientMock->method('getId')->willReturn($clientData['id']);
		$clientMock->method('getName')->willReturn($clientData['name']);
		$clientMock->method('getRedirectUri')->willReturn($clientData['redirectUri']);
		$clientMock->method('getIdentifier')->willReturn($clientData['identifier']);
		$clientMock->method('getSecret')->willReturn($clientData['secret']);
		$clientMock->method('getAllowSubdomains')->willReturn(false);
		$clientMock->method('getTrusted')->willReturn(false);

		$this->clientMapper->method('findAll')->willReturn([$clientMock]);

		$this->commandTester->execute([]);
		$output = $this->commandTester->getDisplay();

		foreach (array_values($clientData) as $clientValue) {
			$this->assertStringContainsString($clientValue, $output);
		}
	}
}
