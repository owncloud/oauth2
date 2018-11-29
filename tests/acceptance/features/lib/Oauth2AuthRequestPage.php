<?php
/**
 * ownCloud
 *
 * @author Artur Neumann <artur@jankaritech.com>
 * @copyright Copyright (c) 2018 Artur Neumann artur@jankaritech.com
 *
 * This code is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License,
 * as published by the Free Software Foundation;
 * either version 3 of the License, or any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU Affero General Public License for more details.
 *
 * You should have received a copy of the GNU Affero General Public License
 * along with this program. If not, see <http://www.gnu.org/licenses/>
 *
 */

namespace Page;

use Behat\Mink\Session;
use SensioLabs\Behat\PageObjectExtension\PageObject\Exception\ElementNotFoundException;

/**
 * Oauth2AuthRequestPage page.
 */
class Oauth2AuthRequestPage extends OwncloudPage {
	private $requestTokenInputXpath = '//input[@name="requesttoken"]';
	private $authorizeButtonXpath = '//button[@type="submit"]';
	private $errorMessageHeadingXpath = '//div[@class="error"]/p[1]';
	private $switchUsersButtonXpath = '//div[@class="error"]//button';

	/**
	 *
	 * @throws ElementNotFoundException
	 *
	 * @return void
	 */
	public function authorizeApp() {
		$submitButton = $this->find("xpath", $this->authorizeButtonXpath);
		
		if ($submitButton === null) {
			throw new ElementNotFoundException(
				__METHOD__ .
				" xpath $this->authorizeButtonXpath " .
				"could not find authorize button"
			);
		}
		$submitButton->click();
	}

	/**
	 *
	 * @throws ElementNotFoundException
	 *
	 * @return void
	 */
	public function switchUsers() {
		$switchUsersButton = $this->find("xpath", $this->switchUsersButtonXpath);
		
		if ($switchUsersButton === null) {
			throw new ElementNotFoundException(
				__METHOD__ .
				" xpath $this->switchUsersButtonXpath " .
				"could not find switch users button"
			);
		}
		$switchUsersButton->click();
	}

	/**
	 *
	 * @throws ElementNotFoundException
	 *
	 * @return string
	 */
	public function getErrorMessageHeading() {
		$errorMessageHeadingElement = $this->find(
			"xpath", $this->errorMessageHeadingXpath
		);
		
		if ($errorMessageHeadingElement === null) {
			throw new ElementNotFoundException(
				__METHOD__ .
				" xpath $this->errorMessageHeadingXpath " .
				"could not find heading of error message"
			);
		}
		
		return $errorMessageHeadingElement->getText();
	}

	/**
	 * @param Session $session
	 * @param int $timeout_msec
	 *
	 * @return void
	 */
	public function waitTillPageIsLoaded(
		Session $session,
		$timeout_msec = STANDARD_UI_WAIT_TIMEOUT_MILLISEC
	) {
		$currentTime = \microtime(true);
		$end = $currentTime + ($timeout_msec / 1000);
		while ($currentTime <= $end) {
			$requestTokenInput = $this->find(
				"xpath", $this->requestTokenInputXpath
			);
			$errorMessageHeadingElement = $this->find(
				"xpath", $this->errorMessageHeadingXpath
			);
			if ($requestTokenInput !== null
				|| $errorMessageHeadingElement !== null
			) {
				break;
			}
			\usleep(STANDARD_SLEEP_TIME_MICROSEC);
			$currentTime = \microtime(true);
		}
		
		if ($currentTime > $end) {
			throw new \Exception(
				__METHOD__ . " timeout waiting for page to load"
			);
		}
	}
}
