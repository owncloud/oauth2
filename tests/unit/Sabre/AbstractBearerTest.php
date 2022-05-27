<?php

namespace OCA\OAuth2\Tests\Unit\Sabre;

use OCA\OAuth2\Sabre\AbstractBearer;
use Sabre\HTTP;

/**
 * Note: This file was imported from sabre/dav 3.2. It was necessary to import
 * it in order to add compatibility with ownCloud 9.1, where an older version of
 * this library is used.
 */
class AbstractBearerTest extends \PHPUnit\Framework\TestCase {
	public function testCheckNoHeaders() {
		$request = new HTTP\Request('GET', '/');
		$response = new HTTP\Response();

		$backend = new AbstractBearerMock();

		$this->assertFalse(
			$backend->check($request, $response)[0]
		);
	}

	public function testCheckInvalidToken() {
		$request = new HTTP\Request('GET', '/', [
			'Authorization' => 'Bearer foo',
		]);
		$response = new HTTP\Response();

		$backend = new AbstractBearerMock();

		$this->assertFalse(
			$backend->check($request, $response)[0]
		);
	}

	public function testCheckSuccess() {
		$request = new HTTP\Request('GET', '/', [
			'Authorization' => 'Bearer valid',
		]);
		$response = new HTTP\Response();

		$backend = new AbstractBearerMock();
		$this->assertEquals(
			[true, 'principals/username'],
			$backend->check($request, $response)
		);
	}

	public function testRequireAuth() {
		$request = new HTTP\Request('GET', '/');
		$response = new HTTP\Response();

		$backend = new AbstractBearerMock();
		$backend->setRealm('writing unittests on a saturday night');
		$backend->challenge($request, $response);

		$this->assertEquals(
			'Bearer realm="writing unittests on a saturday night"',
			$response->getHeader('WWW-Authenticate')
		);
	}
}

class AbstractBearerMock extends AbstractBearer {
	/**
	 * Validates a bearer token.
	 *
	 * This method should return true or false depending on if login
	 * succeeded.
	 *
	 * @param string $bearerToken
	 *
	 * @return bool
	 */
	public function validateBearerToken($bearerToken) {
		return $bearerToken === 'valid' ? 'principals/username' : false;
	}
}
