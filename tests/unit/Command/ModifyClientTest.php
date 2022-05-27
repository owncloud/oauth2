<?php
/**
 * @author Jan Ackermann <jackermann@owncloud.com>
 * @author Jannik Stehle <jstehle@owncloud.com>
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

use OCA\OAuth2\Commands\ModifyClient;
use OCA\OAuth2\Db\Client;
use OCA\OAuth2\Db\ClientMapper;
use OCP\AppFramework\Db\DoesNotExistException;
use Symfony\Component\Console\Tester\CommandTester;
use Test\TestCase;

/**
 * Class ModifyClientTest
 */
class ModifyClientTest extends TestCase {
	/** @var CommandTester */
	private $commandTester;
	/** @var ClientMapper|\PHPUnit\Framework\MockObject\MockObject */
	private $clientMapper;

	protected function setUp(): void {
		parent::setUp();

		$this->clientMapper = $this->createMock(ClientMapper::class);
		$command = new ModifyClient($this->clientMapper);
		$this->commandTester = new CommandTester($command);
	}

	/**
	 * @dataProvider commandDataProvider
	 */
	public function testCommandInput($key, $value, $expectedClientMethodCall) {
		$clientMock = $this->getMockBuilder(Client::class)
			->addMethods([
				'setName',
				'setRedirectUri',
				'setIdentifier',
				'setSecret',
			])
			->getMock();

		$this->clientMapper->method('findByName')->willReturn($clientMock, $this->throwException(new DoesNotExistException('client does not exist')));
		$this->clientMapper->method('findByIdentifier')->willThrowException(new DoesNotExistException('client does not exist'));

		$clientMock->expects($this->once())->method($expectedClientMethodCall)->with($value);

		$this->commandTester->execute([
			'name' => 'iOS',
			'key' => $key,
			'value'=> $value
		]);
	}

	public function commandDataProvider(): array {
		return [
			[ 'name', 'testclient', 'setName'],
			[ 'client-id', 'GO23t7SEhcrffzPrpm5gLfvPbJkKUA3dcMLvNen5IDr2ORQnzFcN8x0MytWPWaAd', 'setIdentifier'],
			[ 'client-secret', 'xHY8hBLHwJzOh8MOjGIjdju9RxWscyjKw2f8k74XkzgKXXnqdDQ0FMG7sU3O2rUl', 'setSecret'],
			[ 'redirect-url', 'http://local', 'setRedirectUri'],
		];
	}
}
