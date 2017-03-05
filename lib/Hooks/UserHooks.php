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

use OC\User\User;
use OCA\OAuth2\Db\AccessTokenMapper;
use OCA\OAuth2\Db\AuthorizationCodeMapper;
use OCA\OAuth2\Db\RefreshTokenMapper;
use OCP\IUserManager;

class UserHooks {

	/** @var IUserManager */
	private $userManager;

	/** @var AuthorizationCodeMapper */
	private $authorizationCodeMapper;

	/** @var  AccessTokenMapper */
	private $accessTokenMapper;

	/** @var RefreshTokenMapper */
	private $refreshTokenMapper;

	/**
	 * UserHooks constructor.
	 *
	 * @param IUserManager $userManager The user manager
	 * @param AuthorizationCodeMapper $authorizationCodeMapper The authorization code mapper
	 * @param AccessTokenMapper $accessTokenMapper The access token mapper
	 * @param RefreshTokenMapper $refreshTokenMapper The refresh token mapper
	 */
	public function __construct(IUserManager $userManager,
								AuthorizationCodeMapper $authorizationCodeMapper,
								AccessTokenMapper $accessTokenMapper,
								RefreshTokenMapper $refreshTokenMapper) {
		$this->userManager = $userManager;
		$this->authorizationCodeMapper = $authorizationCodeMapper;
		$this->accessTokenMapper = $accessTokenMapper;
		$this->refreshTokenMapper = $refreshTokenMapper;
	}

	/**
	 * Registers a pre-delete hook for users to delete authorization codes,
	 * access tokens and refresh tokens that reference the user.
	 */
	public function register() {
		/**
		 * @param User $user
		 */
		$callback = function ($user) {
			if (!is_null($user->getUID())) {
				$this->authorizationCodeMapper->deleteByUser($user->getUID());
				$this->accessTokenMapper->deleteByUser($user->getUID());
				$this->refreshTokenMapper->deleteByUser($user->getUID());
			}
		};

		$this->userManager->listen('\OC\User', 'preDelete', $callback);
	}

}
