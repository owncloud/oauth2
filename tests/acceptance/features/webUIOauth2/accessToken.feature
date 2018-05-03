@webUI @insulated @disablePreviews
Feature: obtaining an access token
	As a user
	I want to be able to receive and use oauth tockens for authentification
	So that I do not need to entrust various apps with my owncloud password

	Background:
		Given these users have been created:
		|username|password|displayname|email       |
		|user1   |1234    |User One   |u1@oc.com.np|

	Scenario: receive an authorization code
		When the user sends an oauth2 authorization request using the webUI
		And the user logs in with username "user1" and password "1234" using the webUI after a redirect from the oauth2AuthRequest page
		And the user authorizes the oauth app using the webUI
		Then the client app should receive an authorization code

	Scenario: receive an access token and use it to access file
		When the user sends an oauth2 authorization request using the webUI
		And the user logs in with username "user1" and password "1234" using the webUI after a redirect from the oauth2AuthRequest page
		And the user authorizes the oauth app using the webUI
		And the client app requests an access token
		Then the client app should be able to download the file "lorem.txt" of "user1" using the access token for authentication

	Scenario: receive a new access token by using the refresh token
		Given the user "user1" has correctly established an oauth session
		When the client app refreshes the access token
		Then the client app should be able to download the file "lorem.txt" of "user1" using the access token for authentication

	Scenario: receive an access token when user is already loged in and use it to access file
		Given the user has browsed to the login page
		And the user has logged in with username "user1" and password "1234" using the webUI
		When the user sends an oauth2 authorization request using the webUI
		And the user authorizes the oauth app using the webUI
		And the client app requests an access token
		Then the client app should be able to download the file "lorem.txt" of "user1" using the access token for authentication

	Scenario: try to receive and authorization code with an unregistered client
		When the user sends an oauth2 authorization request with an unregistered clientId using the webUI
		And the user logs in with username "user1" and password "1234" using the webUI after a redirect from the oauth2AuthRequest page
		Then an invalid oauth request message should be shown

	Scenario: receive an access token for a user that is different to the currently loged in user
		Given these users have been created:
			|username|password|displayname|email       |
			|user2   |1234    |User Two   |u2@oc.com.np|
		And the user has browsed to the login page
		And the user has logged in with username "user1" and password "1234" using the webUI
		When the user "user2" sends an oauth2 authorization request using the webUI
		And the user switches the user to continue the oauth process using the webUI
		And the user logs in with username "user2" and password "1234" using the webUI after a redirect from the oauth2AuthRequest page
		And the user authorizes the oauth app using the webUI
		And the client app requests an access token
		Then the client app should be able to download the file "lorem.txt" of "user2" using the access token for authentication

	Scenario: receive an access token after invalid password entry
		When the user sends an oauth2 authorization request using the webUI
		And the user logs in with username "user1" and invalid password "wrong" using the webUI
		And the user logs in with username "user1" and password "1234" using the webUI after a redirect from the oauth2AuthRequest page
		And the user authorizes the oauth app using the webUI
		And the client app requests an access token
		Then the client app should be able to download the file "lorem.txt" of "user1" using the access token for authentication
		