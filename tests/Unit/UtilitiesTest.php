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

namespace OCA\OAuth2\Tests\Unit;

use OCA\OAuth2\Utilities;
use PHPUnit_Framework_TestCase;

class UtilitiesTest extends PHPUnit_Framework_TestCase {

	public function testGenerateRandom() {
		$random = Utilities::generateRandom();

		$this->assertEquals(64, strlen($random));
		$this->assertFalse(strpos($random, '+'));
		$this->assertFalse(strpos($random, '/'));
	}

	public function testValidateRedirectUri() {
		$this->assertFalse(
			Utilities::validateRedirectUri(
				'http://owncloud.org:80/test?q=1',
				'https://owncloud.org:80/test?q=1',
				false)
		);
		$this->assertTrue(
			Utilities::validateRedirectUri(
				'https://owncloud.org:80/test?q=1',
				'https://sso.owncloud.org:80/test?q=1',
				true)
		);
		$this->assertFalse(
			Utilities::validateRedirectUri(
				'https://owncloud.org:80/test?q=1',
				'https://sso.owncloud.de:80/test?q=1',
				true)
		);
		$this->assertFalse(
			Utilities::validateRedirectUri(
				'https://owncloud.org:80/test?q=1',
				'https://sso.owncloud.org:80/test?q=1',
				false)
		);
		$this->assertFalse(
			Utilities::validateRedirectUri(
				'https://owncloud.org:80/test?q=1',
				'https://owncloud.org:90/test?q=1',
				false)
		);
		$this->assertFalse(
			Utilities::validateRedirectUri(
				'https://owncloud.org:80/tests?q=1',
				'https://owncloud.org:80/test?q=1',
				false)
		);
		$this->assertFalse(
			Utilities::validateRedirectUri(
				'https://owncloud.org:80/test?q=1',
				'https://owncloud.org:80/test?q=0',
				false)
		);
	}

}
