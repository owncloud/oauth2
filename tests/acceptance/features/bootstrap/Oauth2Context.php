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
use Behat\Behat\Hook\Scope\AfterScenarioScope;
use Behat\Behat\Hook\Scope\BeforeScenarioScope;
use Behat\MinkExtension\Context\RawMinkContext;
use Page\Oauth2AuthRequestPage;
use Page\Oauth2OnPersonalSecuritySettingsPage;
use PHPUnit\Framework\Assert;
use TestHelpers\WebDavHelper;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;
use Page\Oauth2AdminSettingsPage;
use TestHelpers\DownloadHelper;
use TestHelpers\SetupHelper;

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
	 * @var Oauth2AdminSettingsPage
	 */
	private $oauth2AdminSettingsPage;
	/**
	 *
	 * @var Oauth2OnPersonalSecuritySettingsPage
	 */
	private $oath2OnPersonalSecurityPage;

	private $clientId
		= 'xdXOt13JKxym1B1QcEncf2XDkLAexMBFwiT9j6EfhhHFJhs2KM9jbjTmf8JBXE69';
	private $clientSecret
		= 'UBntmLjC2yYCeHwsyj73Uwo9TAaecAetRwMw0xYcvNL9yRdLSUi0hUAHfvCHFeFh';
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
	 * @var string[][] array of associated arrays with information about the clients
	 *                 keys: name,redirection_uri,client_id,client_secret,id
	 */
	private $createdOauthClients = [];

	/**
	 *
	 * @param Oauth2AuthRequestPage $oauth2AuthRequestPage
	 * @param Oauth2OnPersonalSecuritySettingsPage $oath2OnPersonalSecurityPage
	 * @param Oauth2AdminSettingsPage $oauth2AdminSettingsPage
	 *
	 * @return void
	 */
	public function __construct(
		Oauth2AuthRequestPage $oauth2AuthRequestPage,
		Oauth2OnPersonalSecuritySettingsPage $oath2OnPersonalSecurityPage,
		Oauth2AdminSettingsPage $oauth2AdminSettingsPage
	) {
		$this->oauth2AuthRequestPage = $oauth2AuthRequestPage;
		$this->oath2OnPersonalSecurityPage = $oath2OnPersonalSecurityPage;
		$this->oauth2AdminSettingsPage = $oauth2AdminSettingsPage;
	}

	/**
	 * @When /^the user(?: "([^"]*)")? sends an oauth2 authorization request using the webUI$/
	 * @Given /^the user(?: "([^"]*)")? has sent an oauth2 authorization request$/
	 *
	 * @param string $username
	 * @param string $clientId
	 *
	 * @return void
	 */
	public function oauthAuthorizationRequestUsingTheWebui(
		$username = null, $clientId = null
	) {
		if ($clientId === null) {
			$clientId = $this->clientId;
		}
		$fullUrl = $this->featureContext->getBaseUrl() .
		'/index.php/apps/oauth2/authorize?response_type=code&client_id=' .
		$clientId .
		'&redirect_uri=http://' .
		$this->redirectUriHost . ':' .
		$this->redirectUriPort;
		if ($username !== null) {
			$fullUrl = $fullUrl . "&user=$username";
		}
		$this->visitPath($fullUrl);
	}

	/**
	 * @When /^the user(?: "([^"]*)")? sends an oauth2 authorization request with the new client-id using the webUI$/
	 *
	 * @param string $username
	 *
	 * @return void
	 */
	public function oauthAuthorizationRequestWithNewClientIdUsingTheWebui(
		$username = null
	) {
		$this->oauthAuthorizationRequestUsingTheWebui(
			$username, \end($this->createdOauthClients)['client_id']
		);
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
		Assert::assertNotSame(
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
	 * @param string|null $clientId
	 * @param string|null $clientSecret
	 *
	 * @return void
	 */
	public function clientAppRequestsAccessToken(
		$refreshToken = null, $clientId = null, $clientSecret = null
	) {
		$redirectUri = \parse_url($this->getSession()->getCurrentUrl());
		\parse_str($redirectUri['query'], $parameters);
		if ($clientId === null) {
			$clientId = $this->clientId;
		}
		if ($clientSecret === null) {
			$clientSecret = $this->clientSecret;
		}
		$client = new Client();
		$options = [];
		$options['auth'] = [$clientId, $clientSecret];
	
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
		$this->accessTokenResponse = \json_decode(
			$response->getBody()->getContents()
		);
	}

	/**
	 * @When the client app requests an access token with the new client-id and client-secret
	 * @Given the client app has requested an access token with the new client-id and client-secret
	 *
	 * @param string $refreshToken see clientAppRequestsAccessToken()
	 *
	 * @return void
	 */
	public function clientAppRequestsAccessTokenWithNewClientId(
		$refreshToken = null
	) {
		$this->clientAppRequestsAccessToken(
			$refreshToken,
			\end($this->createdOauthClients)['client_id'],
			\end($this->createdOauthClients)['client_secret']
		);
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
	 * @When the administrator/user browses to the oauth admin settings page
	 * @Given the administrator/user has browsed to the oauth admin settings page
	 *
	 * @return void
	 */
	public function theUserBrowsesToTheOauth2AdminSettingsPage() {
		$this->oauth2AdminSettingsPage->open();
	}

	/**
	 * @When the administrator/user adds a new oauth client with the name :name and the uri :uri using the webUI
	 *
	 * @param string $name
	 * @param string $uri
	 *
	 * @return void
	 */
	public function addNewOauthClientUsingTheWebUI($name, $uri) {
		$this->oauth2AdminSettingsPage->addClient($name, $uri);
		$client = $this->oauth2AdminSettingsPage->getClientInformationByName($name);
		$this->createdOauthClients[] = $client;
	}

	/**
	 * @Given the administrator has added a new oauth client with the name :name and the uri :uri
	 *
	 * @param string $name
	 * @param string $uri
	 *
	 * @return void
	 */
	public function addNewOauthClient($name, $uri) {
		$this->webUIGeneralContext->adminLogsInUsingTheWebUI();
		$this->theUserBrowsesToTheOauth2AdminSettingsPage();
		$this->addNewOauthClientUsingTheWebUI($name, $uri);
		$this->webUIGeneralContext->theUserLogsOutOfTheWebUI();
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
		$result = WebDavHelper::makeDavRequest(
			$this->featureContext->getBaseUrl(),
			$user, $this->accessTokenResponse->access_token,
			'GET', $file, [], null, null, 2, "files", null, "bearer"
		);
		if (!$should && $result->getStatusCode() < 400) {
			throw new \Exception(
				__METHOD__ . " should not be able to access file, but can"
			);
		}
		if ($should) {
			if ($result->getStatusCode() >= 400) {
				throw new \Exception(
					__METHOD__ . " should be able to access file, but can not"
				);
			}

			if ($this->featureContext->getTokenAuthHasBeenSetTo() === 'true') {
				$adminPassword = $this->featureContext->generateAuthTokenForAdmin();
			} else {
				$adminPassword = $this->featureContext->getAdminPassword();
			}

			$localContent = SetupHelper::readSkeletonFile(
				$file, $this->featureContext->getBaseUrl(),
				$this->featureContext->getAdminUsername(),
				$adminPassword
			);
			$downloadedContent = $result->getBody()->getContents();
			Assert::assertSame(
				$localContent, $downloadedContent,
				__METHOD__ . " content of downloaded file is not as expected"
			);
		}
	}

	/**
	 * @Then the client app should receive an authorization code
	 *
	 * @return void
	 */
	public function clientAppShouldReceiveAuthCode() {
		$redirectUri = \parse_url($this->getSession()->getCurrentUrl());
		Assert::assertEquals(
			$this->redirectUriHost, $redirectUri['host'],
			__METHOD__ . " the host of redirect uri should be '" .
			$this->redirectUriHost . "' but it is '" .
			$redirectUri['host'] . "'"
		);
		Assert::assertEquals(
			$this->redirectUriPort, $redirectUri['port'],
			__METHOD__ . " the port of redirect uri should be '" .
			$this->redirectUriPort . "' but it is '" .
			$redirectUri['port'] . "'"
		);
		\parse_str($redirectUri['query'], $parameters);
		Assert::assertEquals(
			64, \strlen($parameters['code']),
			__METHOD__ . " received code should be 64 char long but its " .
			\strlen($parameters['code']) . " long"
		);
	}

	/**
	 * @Then an invalid oauth request message should be shown
	 *
	 * @return void
	 */
	public function invalidOauthRequestMessageShouldBeShown() {
		$error = $this->oauth2AuthRequestPage->getErrorMessageHeading();
		Assert::assertSame("Request not valid", $error);
	}

	/**
	 * @Then the client app should not be able to refresh the access token
	 *
	 * @return void
	 */
	public function appShouldNotBeAbleToRefreshToken() {
		try {
			$this->refreshAccessToken();
			throw new \Exception(
				__METHOD__ .
				" app should not be able to refresh token but looks like it can"
			);
		} catch (ClientException $e) {
			Assert::assertSame(
				400, $e->getCode(),
				__METHOD__ .
				" expected '400' as HTTP error code, but received '" .
				$e->getCode() . "'"
			);
		}
	}

	/**
	 * @Then a new client with the name :name and the uri :uri should be listed on the webUI
	 *
	 * @param string $name
	 * @param string $uri
	 *
	 * @return void
	 */
	public function assertClientIsListedOnWebUI($name, $uri) {
		$client = $this->oauth2AdminSettingsPage->getClientInformationByName($name);
		Assert::assertSame(
			$name, $client['name'], "name of displayed client is wrong"
		);
		Assert::assertSame(
			$uri, $client['redirection_uri'], "uri of displayed client is wrong"
		);
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
	 * @AfterScenario @webUI
	 *
	 * @param AfterScenarioScope $scope
	 *
	 * @return void
	 */
	public function after(AfterScenarioScope $scope) {
		$this->featureContext->aNewBrowserSessionForHasBeenStarted(
			$this->featureContext->getAdminUsername()
		);
		foreach ($this->createdOauthClients as $createdOauthClient) {
			$this->featureContext->sendRequest(
				"/index.php/apps/oauth2/clients/" .
				$createdOauthClient['id'] .
				"/delete",
				"POST", null, true
			);
		}
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
