@webUI @insulated @disablePreviews
Feature: enforce token auth

As an administrator
I want to be able to enforce token based auth
So that I can improve the security of the system by forbidding basic auth with username & password

	Background:
		Given these users have been created with skeleton files:
			| username    | password | displayname  | email                 |
			| user1       | 1234     | User One     | u1@oc.com.np          |
		And token auth has been enforced
		And user "user1" has correctly established an oauth session

	Scenario: access files app with oauth when token auth is enforced
		When user "user1" requests "/index.php/apps/files" with "GET" using basic auth
		Then the HTTP status code should be "401"
		When the user requests "/index.php/apps/files" with "GET" using oauth
		Then the HTTP status code should be "200"

	Scenario: using WebDAV with oauth when token auth is enforced
		When user "user1" requests "/remote.php/webdav" with "PROPFIND" using basic auth
		Then the HTTP status code should be "401"
		When the user requests "/remote.php/webdav" with "PROPFIND" using oauth
		Then the HTTP status code should be "207"

	Scenario: using OCS with oauth when token auth is enforced
		When user "user1" requests "/ocs/v1.php/apps/files_sharing/api/v1/remote_shares" with "GET" using basic auth
		Then the OCS status code should be "997"
		And the HTTP status code should be "401"
		When the user requests "/ocs/v1.php/apps/files_sharing/api/v1/remote_shares" with "GET" using oauth
		Then the OCS status code should be "100"
		And the HTTP status code should be "200"

	@skip @issue_core_32068
	Scenario: using OCS with oauth when token auth is enforced
		When user "user1" requests "/ocs/v2.php/apps/files_sharing/api/v1/remote_shares" with "GET" using basic auth
		Then the OCS status code should be "401"
		And the HTTP status code should be "401"
		When the user requests "/ocs/v2.php/apps/files_sharing/api/v1/remote_shares" with "GET" using oauth
		Then the OCS status code should be "200"
		And the HTTP status code should be "200"
		
	Scenario Outline: download a file with oauth when token auth is enforced
		Given using <dav_version> DAV path
		When user "user1" downloads file "/lorem.txt" using the WebDAV API
		Then the HTTP status code should be "401"
		But the client app should be able to download the file "lorem.txt" of "user1" using the access token for authentication
		Examples:
			| dav_version   |
			| old           |
			| new           |
