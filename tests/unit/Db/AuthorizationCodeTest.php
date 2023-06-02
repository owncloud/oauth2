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

namespace OCA\OAuth2\Tests\Unit\Db;

use OCA\OAuth2\Db\AuthorizationCode;
use OCA\OAuth2\Exceptions\UnsupportedPkceTransformException;
use OCA\OAuth2\Utilities;
use PHPUnit\Framework\TestCase;

class AuthorizationCodeTest extends TestCase {
	/** @var AuthorizationCode $authorizationCode */
	private $authorizationCode;

	public function setUp(): void {
		parent::setUp();

		$this->authorizationCode = new AuthorizationCode();
	}

	public function testResetExpires() {
		$expected = \time() + AuthorizationCode::EXPIRATION_TIME;
		$this->authorizationCode->resetExpires();
		$this->assertEqualsWithDelta($expected, $this->authorizationCode->getExpires(), 1);
	}

	public function testHasExpired() {
		$this->assertTrue($this->authorizationCode->hasExpired());
		$this->authorizationCode->setExpires(10);
		$this->assertTrue($this->authorizationCode->hasExpired());
		$this->authorizationCode->resetExpires();
		$this->assertFalse($this->authorizationCode->hasExpired());
	}

	public function testIsCodeVerifierValid() {
		$this->authorizationCode->setCodeChallenge("sometext");
		$this->authorizationCode->setCodeChallengeMethod('plain');
		$this->assertTrue($this->authorizationCode->isCodeVerifierValid("sometext"));
		$this->authorizationCode->setCodeChallengeMethod('');
		$this->assertTrue($this->authorizationCode->isCodeVerifierValid("sometext"));
		$this->authorizationCode->setCodeChallengeMethod(null);
		$this->assertTrue($this->authorizationCode->isCodeVerifierValid("sometext"));
		$this->assertFalse($this->authorizationCode->isCodeVerifierValid("othertext"));

		$code_verifier = Utilities::base64Url_encode(\random_bytes(32));
		$code_challenge = Utilities::base64url_encode(\hash('sha256', $code_verifier, true));
		$this->authorizationCode->setCodeChallenge($code_challenge);
		$this->authorizationCode->setCodeChallengeMethod('S256');
		$this->assertTrue($this->authorizationCode->isCodeVerifierValid($code_verifier));
		$this->assertFalse($this->authorizationCode->isCodeVerifierValid("invalid"));
	}

	public function testIsCodeVerifierValidWithUnsupportedMethod() {
		$this->expectException(UnsupportedPkceTransformException::class);
		$this->expectExceptionMessage("Code challenge method invalid not supported");
		$this->authorizationCode->setCodeChallengeMethod("invalid");
		$this->authorizationCode->isCodeVerifierValid("sometext");
	}
}
