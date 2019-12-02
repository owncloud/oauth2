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

use SensioLabs\Behat\PageObjectExtension\PageObject\Exception\ElementNotFoundException;

/**
 * Oauth2-specific items on the admin Settings page.
 */
class Oauth2AdminSettingsPage extends OwncloudPage {
	protected $path = '/index.php/settings/admin?sectionid=authentication#oauth2';
	private $oauthAppNameInputId = "name";
	private $oauthRedirectionUriInputId = "redirect_uri";
	private $allowSubdomainsCheckBoxXpath = "//label[@for='allow_subdomains']";
	private $addClientBtnXpath = "//*[@id='oauth2']//button[@id='oauth2_submit']";
	private $clientRowByNameXpath = "//*[@id='oauth2']//td[text()='%s']/../*";

	/**
	 *
	 * @param string $appName
	 * @param string $redirctionUri
	 * @param boolean $allowSubdomains
	 *
	 * @throws ElementNotFoundException
	 *
	 * @return void
	 */
	public function addClient($appName, $redirctionUri, $allowSubdomains = false) {
		$this->fillField($this->oauthAppNameInputId, $appName);
		$this->fillField($this->oauthRedirectionUriInputId, $redirctionUri);
		if ($allowSubdomains === true) {
			$allowSubdomainsCheckBox = $this->find(
				"xpath", $this->allowSubdomainsCheckBoxXpath
			);
			if ($allowSubdomainsCheckBox === null) {
				throw new ElementNotFoundException(
					__METHOD__ .
					"xpath: " . $this->allowSubdomainsCheckBoxXpath .
					" could not find checkbox to allow subdomains"
				);
			}
			$allowSubdomainsCheckBox->click();
		}
		$addClientBtn = $this->find("xpath", $this->addClientBtnXpath);
		if ($addClientBtn === null) {
			throw new ElementNotFoundException(
				__METHOD__ .
				"xpath: " . $this->addClientBtnXpath .
				" could not find button to add oauth clients"
			);
		}
		$addClientBtn->click();
	}

	/**
	 *
	 * @param string $name
	 *
	 * @throws ElementNotFoundException
	 *
	 * @return string[] arrray with keys name,redirection_uri,client_id,client_secret,id
	 */
	public function getClientInformationByName($name) {
		$xpath = \sprintf($this->clientRowByNameXpath, $name);
		$tds = $this->findAll("xpath", $xpath);
		if (\count($tds) === 0) {
			throw new ElementNotFoundException(
				__METHOD__ .
				"xpath: " . $xpath .
				" could not find row of a client"
			);
		}
		$result['name'] = $tds[0]->getText();
		$result['redirection_uri'] = $tds[1]->getText();
		$result['client_id'] = $tds[2]->getText();
		$result['client_secret'] = $tds[3]->getText();
		$result['id'] = (int) $tds[5]->find("xpath", "/button")->getAttribute("data-id");

		return $result;
	}
}
