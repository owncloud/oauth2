# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](http://keepachangelog.com/en/1.0.0/).

## [0.3.0] - unreleased

### Added
- Expiry of refresh tokens [#174](https://github.com/owncloud/oauth2/pull/174)
- Autofocus of buttons [#173](https://github.com/owncloud/oauth2/pull/173)
- Support implicit grant [#166](https://github.com/owncloud/oauth2/pull/166)
- Add occ commands to add and remove clients [#178](https://github.com/owncloud/oauth2/pull/178) + [#166](https://github.com/owncloud/oauth2/pull/166)

## [0.2.3] - 2018-08-09

### Fixed
- Erroneous ownCloud 2.4.2 client behavior causing service interruptions [#145](https://github.com/owncloud/oauth2/pull/145)
- Initialization for password-less sessions [#129](https://github.com/owncloud/oauth2/pull/129)

## [0.2.2]

### Added
- OpenID Connect UserInfo endpoint [#115](https://github.com/owncloud/oauth2/pull/115)

### Fixed
- Expired token causing server failures  [#118](https://github.com/owncloud/oauth2/pull/118)

## [0.2.1] - 2017-11-28

### Fixed

- OAuth app blocking public uploads [#100](https://github.com/owncloud/oauth2/pull/100)

## [0.2.0] - 2017-10-13

### Added

- Oracle and 4-byte MySQL support - [#42](https://github.com/owncloud/oauth2/pull/42)
- Predefined client ids for mobile and desktop clients - [#38](https://github.com/owncloud/oauth2/pull/38)

### Changed

- Allow multiple tokens per client - [#65](https://github.com/owncloud/oauth2/pull/65)

### Fixed

- Security Hardening - [#71](https://github.com/owncloud/oauth2/pull/71)
- Verify Bearer token even if the session is still valid - [#53](https://github.com/owncloud/oauth2/pull/53)
- Use displayname on switch user screen - [#90](https://github.com/owncloud/oauth2/pull/90)

[Unreleased]: https://github.com/owncloud/oauth2/compare/v0.2.3...HEAD
[0.2.3]: https://github.com/owncloud/oauth2/compare/v0.2.2...v0.2.3
[0.2.2]: https://github.com/owncloud/oauth2/compare/v0.2.1...v0.2.2
[0.2.1]: https://github.com/owncloud/oauth2/compare/v0.2.0...v0.2.1
[0.2.0]: https://github.com/owncloud/oauth2/compare/v0.1.0...v0.2.0
