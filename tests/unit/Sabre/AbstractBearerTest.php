<?php

namespace OCA\OAuth2\Sabre;

use Sabre\HTTP;
use Test\TestCase;

/**
 * Note: This file was imported from sabre/dav 3.2. It was necessary to import
 * it in order to add compatibility with ownCloud 9.1, where an older version of
 * this library is used.
 */
class AbstractBearerTest extends TestCase {

	function testCheckNoHeaders() {

		$request = new HTTP\Request();
		$response = new HTTP\Response();

		$backend = new AbstractBearerMock();

		$this->assertFalse(
			$backend->check($request, $response)[0]
		);

	}

	function testCheckInvalidToken() {

		$request = HTTP\Sapi::createFromServerArray([
			'HTTP_AUTHORIZATION' => 'Bearer foo',
		]);
		$response = new HTTP\Response();

		$backend = new AbstractBearerMock();

		$this->assertFalse(
			$backend->check($request, $response)[0]
		);

	}

	function testCheckSuccess() {

		$request = HTTP\Sapi::createFromServerArray([
			'HTTP_AUTHORIZATION' => 'Bearer valid',
		]);
		$response = new HTTP\Response();

		$backend = new AbstractBearerMock();
		$this->assertEquals(
			[true, 'principals/username'],
			$backend->check($request, $response)
		);

	}

	function testRequireAuth() {

		$request = new HTTP\Request();
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
	 * Validates a bearer token
	 *
	 * This method should return true or false depending on if login
	 * succeeded.
	 *
	 * @param string $bearerToken
	 * @return bool
	 */
	function validateBearerToken($bearerToken) {

		return 'valid' === $bearerToken ? 'principals/username' : false;

	}

}
