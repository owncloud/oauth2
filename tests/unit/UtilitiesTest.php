<?php
/**
 * @author Project Seminar "sciebo@Learnweb" of the University of Muenster
 * @copyright Copyright (c) 2017, University of Muenster
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
use PHPUnit\Framework\TestCase;

class UtilitiesTest extends TestCase {
	public function testGenerateRandom(): void {
		$random = Utilities::generateRandom();

		$this->assertEquals(64, \strlen($random));
		$this->assertFalse(\strpos($random, '+'));
		$this->assertFalse(\strpos($random, '/'));
	}

	public function providesUrlsToValidate(): array {
		return [
			[false, 'http://owncloud.org:80/test?q=1', 'https://owncloud.org:80/test?q=1', false],
			[true, 'https://owncloud.org:80/test?q=1', 'https://sso.owncloud.org:80/test?q=1', true],
			[false, 'https://owncloud.org:80/test?q=1', 'https://sso.owncloud.de:80/test?q=1', true],
			[false, 'https://owncloud.org:80/test?q=1', 'https://sso.owncloud.org:80/test?q=1', false],
			[false, 'https://owncloud.org:80/test?q=1', 'https://owncloud.org:90/test?q=1', false],
			[false, 'https://owncloud.org:80/tests?q=1', 'https://owncloud.org:80/test?q=1', false],
			[false, 'https://owncloud.org:80/test?q=1', 'https://owncloud.org:80/test?q=0', false],
			[true, 'http://localhost:*/test?q=1', 'http://localhost:12345/test?q=1', false],
			[false, 'http://excepted.com', 'http://aaa\@excepted.com', false],
			[false, 'https://trustedclient.com', 'https://munity.trustedclient.community.', true],
			[false, 'https://trustedclient.com', 'https://munity.trustedclient.community', true]
		];
	}

	/**
	 * @dataProvider providesUrlsToValidate
	 * @param $expectedResult
	 * @param $expectedRedirect
	 * @param $actualRedirect
	 * @param $allowSubDomain
	 */
	public function testValidateRedirectUri($expectedResult, $expectedRedirect, $actualRedirect, $allowSubDomain): void {
		$this->assertEquals(
			$expectedResult,
			Utilities::validateRedirectUri(
				$expectedRedirect,
				$actualRedirect,
				$allowSubDomain
			)
		);
	}

	public function providesUrls(): array {
		return [
			[true, 'http://localhost:*'],
			[true, 'http://localhost:*/oc/10.0'],
			[true, 'oc://com.android'],
			[true, 'file://test.txt'],
			[false, 'x'],
			[false, 'http://owncloud.org:*'],
		];
	}

	/**
	 * @dataProvider providesUrls
	 * @param $expected
	 * @param $url
	 */
	public function testIsValidUrl($expected, $url): void {
		$this->assertEquals($expected, Utilities::isValidUrl($url));
	}

	public function testBase64url_encode(): void {
		$data = \random_bytes(32);
		$encoded = Utilities::base64url_encode($data);
		$this->assertEquals(43, \strlen($encoded));
		$this->assertFalse(\strpos($encoded, '+'));
		$this->assertFalse(\strpos($encoded, '/'));
		$this->assertFalse(\strpos($encoded, '='));
	}
}
