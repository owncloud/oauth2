@webUI @insulated @disablePreviews
Feature: register a new client
  As an admin
  I want to be able to register new clients
  So that private set of client_ids and client_secrets can be used

  Scenario: register a new client on the webUI
    Given user admin has logged in using the webUI
    And the administrator has browsed to the oauth admin settings page
    When the administrator adds a new oauth client with the name "new client" and the uri "http://localhost:*" using the webUI
    Then a new client with the name "new client" and the uri "http://localhost:*" should be listed on the webUI

  Scenario: oauth authorization with a new client
    Given these users have been created with skeleton files:
      | username | password | displayname  | email             |
      | Alice    | 1234     | Alice Hansen | alice@example.org |
      | Brian    | 1234     | Brian Murphy | brian@example.org |
    And the administrator has added a new oauth client with the name "client1" and the uri "http://localhost:*"
    When the user sends an oauth2 authorization request with the new client-id using the webUI
    And the user logs in with username "Alice" and password "1234" using the webUI after a redirect from the oauth2AuthRequest page
    And the user authorizes the oauth app using the webUI
    And the client app requests an access token with the new client-id and client-secret
    Then the client app should be able to download the file "lorem.txt" of "Alice" using the access token for authentication
    But the client app should not be able to download the file "lorem.txt" of "Brian" using the access token for authentication
