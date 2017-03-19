<?php
/**
 * @author Project Seminar "sciebo@Learnweb" of the University of Muenster
 * @copyright Copyright (c) 2017, University of Muenster
 * @license AGPL-3.0
 *
 * This code is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License, version 3,
 * as published by the Free Software Foundation.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU Affero General Public License for more details.
 *
 * You should have received a copy of the GNU Affero General Public License, version 3,
 * along with this program.  If not, see <http://www.gnu.org/licenses/>
 */

namespace OCA\OAuth2;

class Utilities {

    /**
     * Generates a random string with 64 characters.
     *
     * @return string The random string.
     */
    public static function generateRandom() {
        return \OC::$server->getSecureRandom()->generate(64,
            'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789');
    }

	/**
	 * Validates a redirection URI.
	 *
	 * @param string $expected The expected redirection URI.
	 * @param string $actual The actual redirection URI.
	 * @param boolean $allowSubdomains Whether to allow subdomains.
	 *
	 * @return boolean True if the redirection URI is valid, false otherwise.
	 */
	public static function validateRedirectUri($expected, $actual, $allowSubdomains) {
		if (strcmp(parse_url($expected, PHP_URL_SCHEME), parse_url($actual, PHP_URL_SCHEME)) !== 0) {
			return false;
		}

		$expectedHost = parse_url($expected, PHP_URL_HOST);
		$actualHost = parse_url($actual, PHP_URL_HOST);

		if ($allowSubdomains) {
			if (strcmp($expectedHost, $actualHost) !== 0
				&& strcmp($expectedHost, str_replace(explode('.', $actualHost)[0] . '.', '', $actualHost)) !== 0
			) {
				return false;
			}
		} else {
			if (strcmp($expectedHost, $actualHost) !== 0) {
				return false;
			}
		}

		if (strcmp(parse_url($expected, PHP_URL_PORT), parse_url($actual, PHP_URL_PORT)) !== 0) {
			return false;
		}

		if (strcmp(parse_url($expected, PHP_URL_PATH), parse_url($actual, PHP_URL_PATH)) !== 0) {
			return false;
		}

		if (strcmp(parse_url($expected, PHP_URL_QUERY), parse_url($actual, PHP_URL_QUERY)) !== 0) {
			return false;
		}

		return true;
	}

}
