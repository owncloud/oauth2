<?php
/**
 * @author Lukas Biermann
 * @author Nina Herrmann
 * @author Wladislaw Iwanzow
 * @author Dennis Meis
 * @author Jonathan Neugebauer
 *
 * @copyright Copyright (c) 2016, Project Seminar "PSSL16" at the University of Muenster.
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

namespace OCA\OAuth2\Hooks;

use OCA\OAuth2\Db\AccessTokenMapper;
use OCP\IUserManager;
use OC\User\User;

class UserHooks {

	/** @var IUserManager */
	private $userManager;

	/** @var  AccessTokenMapper */
	private $accessTokenMapper;

	/**
	 * UserHooks constructor.
	 *
	 * @param IUserManager $userManager
	 * @param AccessTokenMapper $accessTokenMapper
	 */
	public function __construct(IUserManager $userManager,
								AccessTokenMapper $accessTokenMapper){
		$this->userManager = $userManager;
		$this->accessTokenMapper = $accessTokenMapper;

	}

	public function register() {
		/**
		 * @param User $user
		 */
		$callback = function($user) {
			// your code that executes before $user is deleted
			if (null !== ($user->getUID())){
			$this->accessTokenMapper->deleteByUser($user->getUID());}
		};
		$this->userManager->listen('\OC\User', 'preDelete', $callback);
	}


}