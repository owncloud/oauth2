@webUI @insulated @disablePreviews
Feature: rewoke an access token
	As a user
	I want to be able to revoke an oauth tokens
	So that I do a can stop a previous permitted application to access my data

	Background:
		Given these users have been created:
		|username|password|displayname|email       |
		|user1   |1234    |User One   |u1@oc.com.np|

	Scenario: rewoke an access token by webUI
		Given the user "user1" has correctly established an oauth session
		And the user has browsed to the personal security settings page
		When the user revokes the oauth app "Desktop Client" using the webUI
		Then the client app should not be able to download the file "lorem.txt" of "user1" using the access token for authentication
