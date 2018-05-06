<?php
/**
 * ownCloud
 *
 * @author Artur Neumann <artur@jankaritech.com>
 * @copyright Copyright (c) 2018 Artur Neumann artur@jankaritech.com
 *
 * This library is free software; you can redistribute it and/or
 * modify it under the terms of the GNU AFFERO GENERAL PUBLIC LICENSE
 * License as published by the Free Software Foundation; either
 * version 3 of the License, or any later version.
 *
 * This library is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU AFFERO GENERAL PUBLIC LICENSE for more details.
 *
 * You should have received a copy of the GNU Affero General Public
 * License along with this library.  If not, see <http://www.gnu.org/licenses/>.
 *
 */

use Behat\Behat\Context\Context;
use Behat\Behat\Hook\Scope\BeforeScenarioScope;
use Behat\MinkExtension\Context\RawMinkContext;
use Page\Oauth2AuthRequestPage;
use Page\Oauth2OnPersonalSecuritySettingsPage;
use TestHelpers\WebDavHelper;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;

require_once 'bootstrap.php';

/**
 * Context keeping steps for oauth tests
 *
 */
class Oauth2Context extends RawMinkContext implements Context {
	
	/**
	 * 
	 * @var FeatureContext
	 */
	private $featureContext;
	/**
	 * 
	 * @var WebUIGeneralContext
	 */
	private $webUIGeneralContext;
	/**
	 * 
	 * @var WebUILoginContext
	 */
	private $webUILoginContext;
	/**
	 * 
	 * @var Oauth2AuthRequestPage
	 */
	private $oauth2AuthRequestPage;
	
	/**
	 * 
	 * @var Oauth2OnPersonalSecuritySettingsPage
	 */
	private $oath2OnPersonalSecurityPage;

	private $clientId = 'xdXOt13JKxym1B1QcEncf2XDkLAexMBFwiT9j6EfhhHFJhs2KM9jbjTmf8JBXE69';
	private $clientSecret = 'UBntmLjC2yYCeHwsyj73Uwo9TAaecAetRwMw0xYcvNL9yRdLSUi0hUAHfvCHFeFh';
	private $redirectUriPort;
	private $redirectUriHost = "localhost";
	/**
	 * 
	 * @var stdClass
	 * attributes: 
	 * access_token, token_type, expires_in, refresh_token, user_id, message_url
	 * @link https://github.com/owncloud/oauth2#protocol-flow
	 */
	private $accessTokenResponse;
	
	/**
	 * 
	 * @param Oauth2AuthRequestPage $oauth2AuthRequestPage
	 * 
	 * @return void
	 */
	public function __construct(
		Oauth2AuthRequestPage $oauth2AuthRequestPage,
		Oauth2OnPersonalSecuritySettingsPage $oath2OnPersonalSecurityPage
	) {
		$this->oauth2AuthRequestPage = $oauth2AuthRequestPage;
		$this->oath2OnPersonalSecurityPage = $oath2OnPersonalSecurityPage;
	}

	/**
	 * @When /^the user(?: "([^"]*)")? sends an oauth2 authorization request using the webUI$/
	 * @Given /^the user(?: "([^"]*)")? has sent an oauth2 authorization request$/
	 * 
	 * @param string $username
	 * 
	 * @return void
	 */
	public function oauthAuthorizationRequestUsingTheWebui($username = null) {
		$fullUrl = $this->featureContext->getBaseUrl() .
		'/index.php/apps/oauth2/authorize?response_type=code&client_id=' .
		$this->clientId .
		'&redirect_uri=http://' .
		$this->redirectUriHost . ':' .
		$this->redirectUriPort;
		if ($username !== null) {
			$fullUrl = $fullUrl . "&user=$username";
		}
		$this->visitPath($fullUrl);
	}

	/**
	 * @When the user sends an oauth2 authorization request with an unregistered clientId using the webUI
	 *
	 * @return void
	 */
	public function oAuthAuthorizationRequestUnregisteredClientIdUsingTheWebui() {
		$this->visitPath(
			$this->featureContext->getBaseUrl() .
			'/index.php/apps/oauth2/authorize?response_type=code&client_id=xxxxxx' .
			'&redirect_uri=http://' .
			$this->redirectUriHost . ':' .
			$this->redirectUriPort
		);
	}

	/**
	 * @When the user authorizes the oauth app using the webUI
	 * 
	 * @return void
	 */
	public function theUserAuthorizesOauthAppUsingTheWebUI() {
		$this->oauth2AuthRequestPage->authorizeApp();
	}

	/**
	 * @Given the user :user has correctly established an oauth session
	 * 
	 * @param string $user
	 * 
	 * @return void
	 */
	public function establishOauthSession($user) {
		$this->oauthAuthorizationRequestUsingTheWebui();
		$this->webUILoginContext
			->theUserLogsInWithUsernameAndPasswordAfterRedirectFromThePage(
				$user,
				$this->featureContext->getPasswordForUser($user),
				"oauth2AuthRequest"
			);
		$this->theUserAuthorizesOauthAppUsingTheWebUI();
		$this->clientAppRequestsAccessToken();
	}

	/**
	 * @When the client app refreshes the access token
	 * 
	 * @return void
	 */
	public function refreshAccessToken() {
		$oldAccessToken = $this->accessTokenResponse->access_token;
		$this->clientAppRequestsAccessToken(
			$this->accessTokenResponse->refresh_token
		);
		PHPUnit_Framework_Assert::assertNotSame(
			$oldAccessToken, $this->accessTokenResponse->access_token,
			__METHOD__ . " the new token is not different to the old one"
		);
	}

	/**
	 * @When the user switches the user to continue the oauth process using the webUI
	 *
	 * @return void
	 */
	public function switchTheUserToContinueOauthProcess() {
		$this->oauth2AuthRequestPage->switchUsers();
	}

	/**
	 * @When the client app requests an access token
	 * @Given the client app has requested an access token
	 * 
	 * @param string $refreshToken if set the `grant_type` `refresh_token`
	 *                             will be used with the given refresh token
	 *                             to request a new access token
	 * 
	 * @return void
	 */
	public function clientAppRequestsAccessToken($refreshToken = null) {
		$redirectUri = \parse_url($this->getSession()->getCurrentUrl());
		parse_str($redirectUri['query'], $parameters);
		$client = new Client();
		$options = [];
		$options['auth'] = [$this->clientId, $this->clientSecret];
	
		if ($refreshToken === null) {
			$options['body'] = [
				'grant_type' => 'authorization_code',
				'code' => $parameters['code'],
				'redirect_uri' => 'http://' . $this->redirectUriHost . ':' .
				$this->redirectUriPort
			];
		} else {
			$options['body'] = [
				'grant_type' => 'refresh_token',
				'refresh_token' => $refreshToken
			];
		}

		$fullUrl = $this->featureContext->getBaseUrl() .
				   '/index.php/apps/oauth2/api/v1/token';
		$request = $client->createRequest('POST', $fullUrl, $options);
		$response = $client->send($request);
		$this->accessTokenResponse = json_decode($response->getBody()->getContents());
	}

	/**
	 * @When the user requests :url with :method using oauth
	 * @Given the user has requested :url with :method using oauth
	 *
	 * @param string $url
	 * @param string $method
	 *
	 * @return void
	 */
	public function userRequestsURLWithOAuth($url, $method) {
		$this->featureContext->sendRequest(
			$url, $method, 'Bearer ' . $this->accessTokenResponse->access_token
		);
	}

	/**
	 * @When the user revokes the oauth app :appName using the webUI
	 * 
	 * @param string $appName
	 * 
	 * @return void
	 */
	public function revokeOauthAppUsingTheWebUI($appName) {
		$this->oath2OnPersonalSecurityPage->revokeApp(
			$this->getSession(), $appName
		);
	}
	/**
	 * @Then /^the client app should (not|)\s?be able to download the file ((?:'[^']*')|(?:"[^"]*")) of ((?:'[^']*')|(?:"[^"]*")) using the access token for authentication$/
	 *
	 * @param string $shouldOrNot
	 * @param string $file
	 * @param string $user
	 * 
	 * @return void
	 */
	public function accessFileUsingOauthToken($shouldOrNot, $file, $user) {
		$should = ($shouldOrNot !== "not");
		// The capturing groups of the regex include the quotes at each
		// end of the captured string, so trim them.
		if ($user !== "") {
			$user = \trim($user, $user[0]);
		}
		if ($file !== "") {
			$file = \trim($file, $file[0]);
		}
		try {
			$result = WebDavHelper::makeDavRequest(
				$this->featureContext->getBaseUrl(),
				$user, $this->accessTokenResponse->access_token,
				'GET', $file, [], null, null, 2, "files", null, "bearer"
			);
			if (!$should) {
				throw new \Exception(
					__METHOD__ . " should not be able to access file, but can"
				);
			}
			$originalFile = \getenv("SRC_SKELETON_DIR") . "/" . \trim($file);
			$localContent = \file_get_contents($originalFile);
			$downloadedContent = $result->getBody()->getContents();
			PHPUnit_Framework_Assert::assertSame(
				$localContent, $downloadedContent,
				__METHOD__ . " content of downloaded file is not as expected"
			);
		} catch (ClientException $e) {
			if ($should) {
				throw new \Exception(
					__METHOD__ . " should be able to access file, but can not"
				);
			}
		}
	}

	/**
	 * @Then the client app should receive an authorization code
	 * 
	 * @return void
	 */
	public function clientAppShouldReceiveAuthCode() {
		$redirectUri = \parse_url($this->getSession()->getCurrentUrl());
		PHPUnit_Framework_Assert::assertEquals(
			$this->redirectUriHost, $redirectUri['host'],
			__METHOD__ . " the host of redirect uri should be '" .
			$this->redirectUriHost . "' but it is '" .
			$redirectUri['host'] . "'"
		);
		PHPUnit_Framework_Assert::assertEquals(
			$this->redirectUriPort, $redirectUri['port'],
			__METHOD__ . " the port of redirect uri should be '" .
			$this->redirectUriPort . "' but it is '" .
			$redirectUri['port'] . "'"
		);
		parse_str($redirectUri['query'], $parameters);
		PHPUnit_Framework_Assert::assertEquals(
			64, strlen($parameters['code']), 
			__METHOD__ . " received code should be 64 char long but its " .
			strlen($parameters['code']) . " long"
		);
	}

	/**
	 * @Then an invalid oauth request message should be shown
	 * 
	 * @return void
	 */
	public function invalidOauthRequestMessageShouldBeShown() {
		$error = $this->oauth2AuthRequestPage->getErrorMessageHeading();
		PHPUnit_Framework_Assert::assertSame("Request not valid", $error);
	}

	/**
	 * This will run before EVERY scenario.
	 * It will set the properties for this object.
	 *
	 * @BeforeScenario @webUI
	 *
	 * @param BeforeScenarioScope $scope
	 *
	 * @return void
	 */
	public function before(BeforeScenarioScope $scope) {
		// Get the environment
		$environment = $scope->getEnvironment();
		// Get all the contexts you need in this context
		$this->featureContext = $environment->getContext('FeatureContext');
		$this->webUIGeneralContext = $environment->getContext('WebUIGeneralContext');
		$this->webUILoginContext = $environment->getContext('WebUILoginContext');
		$this->redirectUriPort = $this->findAvailablePort();
	}

	/**
	 * finds an available network port
	 * 
	 * @return int port number
	 */
	private function findAvailablePort() {
		$socket = \socket_create_listen(0);
		\socket_getsockname($socket, $address, $port);
		\socket_close($socket);
		return $port;
	}
}
