# OAuth 2.0

This App implements the [OAuth 2.0 Authorization Code Flow](https://tools.ietf.org/html/rfc6749#section-4.1).

## Installing the app
Place the content of this repository in **owncloud/apps/oauth2**.

## Using the app

### Endpoints
* Authorization URL: `/index.php/apps/oauth2/authorize`
* Access Token URL: `/index.php/apps/oauth2/api/v1/token`

### Protocol Flow
1. [Client registration](https://tools.ietf.org/html/rfc6749#section-2): First the clients have to be registered in the admin settings: `/index.php/settings/admin#oauth2`. You need to specify a name for the client (the name is unrelated to the OAuth 2.0 protocol and is just used to recognize it later) and the redirect URI. A client identifier and client secret is being generated when adding a new client. They both consist of 64 characters.

2. [Authorization Request](https://tools.ietf.org/html/rfc6749#section-4.1.1): For every registered client an Authorization Request can be made. The client redirects the resource owner to the [Authorization URL](#endpoints) and requests authorization. The following URL parameters have to be specified: 
    1. `response_type` (required): needs to be `code` because at this time only the Authorization Code Flow is implemented.
    2. `client_id` (required): the client identifier obtained when registering the client.
    3. `redirect_uri` (required): the redirect URI specified when registering the client.
    4. `state` (optional): can be set by the client "to maintain state between the request and callback" ([RFC 6749](https://tools.ietf.org/html/rfc6749#section-4.1.1)).

3. [Authorization Response](https://tools.ietf.org/html/rfc6749#section-4.1.2): After the resource owner's authorization the apps redirects to the `redirect_uri` specified in the Authorization Request and adds the Authorization Code as URL parameter `code`.

4. [Access Token Request](https://tools.ietf.org/html/rfc6749#section-4.1.3): With the Authorization Code the client can request an Access Token using the [Access Token URL](#endpoints). [Client Authentication](https://tools.ietf.org/html/rfc6749#section-2.3) is done using Basic Auth with the client identifier as username and the client secret as password.

5. [Access Token Response](https://tools.ietf.org/html/rfc6749#section-4.1.4): The app responses to a valid Access Token Request with an JSON response like this:

```json
{
    "access_token" : "1vtnuo1NkIsbndAjVnhl7y0wJha59JyaAiFIVQDvcBY2uvKmj5EPBEhss0pauzdQ",
    "token_type" : "Bearer",
    "user_id" : "admin"
}
```

There are tables for saving Clients, Authorization Codes, Access Tokens and Refresh Tokens in the database.

## Issues to be solved
- [ ] Under some configurations there was an error that the [`ClientMapper`](/db/ClientMapper.php) could not be found (Ubuntu + PHP 7 + nginx + PostgreSQL, Ubuntu + PHP 7 + Apache + MySQL). But installing in a fresh [Docker container](https://hub.docker.com/_/owncloud/) was successful. The reasons for this error still have to be investigated.

## To Do
- [ ] Add PHPUnit tests.
- [ ] Add section in personal settings for managing authorized applications.
- [ ] Add option for [Refresh Tokens](https://tools.ietf.org/html/rfc6749#section-1.5).
- [ ] Add option for an expiration time for Authorization Codes, Access Tokens and Refresh Tokens.
