<?php
/**
 * ownCloud - oauth2
 *
 * This file is licensed under the Affero General Public License version 3 or
 * later. See the COPYING file.
 *
 * @author Lukas Biermann
 * @copyright Lukas Biermann 2016
 */

namespace OCA\OAuth2\Controller;
use OCP\AppFramework\Controller;
use OCP\IConfig;
use OCP\IRequest;

class SettingsController extends Controller {

	/**
	 * @var IConfig
	 */
	private $config;
	public function __construct($AppName, IRequest $request, IConfig $config) {
		parent::__construct($AppName, $request);
		$this->config = $config;
	}


	/**
	 * Place to transfer the data out of the admin.php to the database.
	 * The real database implementation is not done yet.
	 *
	 */
	public function transferCredentials($PHP_AUTH_USER, $PHP_AUTH_SECRET){
		if(isset($_POST[' PHP_AUTH_USER' ]) && isset($_POST[ 'PHP_AUTH_SECRET' ])){
			return new JSONResponse(['message' => 'Successfully committed your credentials.']);
		} else {
			return new JSONResponse(['message' => 'Unknown User or Secret.'], Http::STATUS_BAD_REQUEST);
		}
	}


}