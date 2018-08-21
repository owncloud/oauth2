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
 * Oauth2-specific items on the Personal Security Settings page.
 */
class Oauth2OnPersonalSecuritySettingsPage extends OwncloudPage {
	private $deleteBtnByAppNameXpath
		= '//td[text()="%s"]/..//input[contains(@class,"delete")]';

	/**
	 *
	 * @param Session $session
	 * @param string $app
	 *
	 * @throws ElementNotFoundException
	 *
	 * @return void
	 */
	public function revokeApp(Session $session, $app) {
		$xpath = \sprintf($this->deleteBtnByAppNameXpath, $app);
		$revokeBtn = $this->find("xpath", $xpath);
		if ($revokeBtn === null) {
			throw new ElementNotFoundException(
				__METHOD__ .
				" xpath $xpath " .
				"could not find revoke button"
			);
		}
		$revokeBtn->click();
		$session->getDriver()->getWebDriverSession()->accept_alert();
		$this->waitForAjaxCallsToStartAndFinish($session);
	}
}
