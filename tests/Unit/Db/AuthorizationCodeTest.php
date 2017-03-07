<?php
/**
 * @author Lukas Biermann
 * @author Nina Herrmann
 * @author Wladislaw Iwanzow
 * @author Dennis Meis
 * @author Jonathan Neugebauer
 *
 * @copyright Copyright (c) 2017, Project Seminar "PSSL16" at the University of Muenster.
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

namespace OCA\OAuth2\Tests\Unit\Db;

use OCA\OAuth2\Db\AuthorizationCode;
use PHPUnit_Framework_TestCase;

class AuthorizationCodeTest extends PHPUnit_Framework_TestCase {

	/** @var AuthorizationCode $authorizationCode */
	private $authorizationCode;

	public function setUp() {
		parent::setUp();

		$this->authorizationCode = new AuthorizationCode();
	}

	public function testResetExpires() {
		$expected = time() + 600;
		$this->authorizationCode->resetExpires();
		$this->assertEquals($expected, $this->authorizationCode->getExpires(), '', 1);
	}

	public function testHasExpired() {
		$this->assertTrue($this->authorizationCode->hasExpired());
		$this->authorizationCode->setExpires(10);
		$this->assertTrue($this->authorizationCode->hasExpired());
		$this->authorizationCode->resetExpires();
		$this->assertFalse($this->authorizationCode->hasExpired());
	}

}
