@webUI @insulated @disablePreviews
Feature: revoke an access token
  As a user
  I want to be able to revoke an oauth token
  So that I can stop a previous permitted application accessing my data

  Background:
    Given these users have been created with large skeleton files:
      | username | password | displayname  | email             |
      | Alice    | 1234     | Alice Hansen | alice@example.org |


  Scenario: revoke an access token by webUI
    Given user "Alice" has correctly established an oauth session
    And the user has browsed to the personal security settings page
    When the user revokes the oauth app "Desktop Client" using the webUI
    Then the client app should not be able to download the file "lorem.txt" of "Alice" using the access token for authentication


  Scenario: receiving a new access token by using the refresh token should not work after revoking the app
    Given user "Alice" has correctly established an oauth session
    And the user has browsed to the personal security settings page
    When the user revokes the oauth app "Desktop Client" using the webUI
    Then the client app should not be able to refresh the access token
