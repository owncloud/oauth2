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

namespace OCA\OAuth2\Sabre;

use OC\User\Session;
use OCA\DAV\Connector\Sabre\Auth;
use OCP\IRequest;
use OCP\ISession;
use Sabre\DAV\Auth\Backend\AbstractBearer;

/**
 * OAuth 2.0 authentication backend class.
 */
class OAuth2 extends AbstractBearer {

	const DAV_AUTHENTICATED = Auth::DAV_AUTHENTICATED;

	/**
	 * This is the prefix that will be used to generate principal urls.
	 *
	 * @var string
	 */
	protected $principalPrefix;

	/** @var ISession */
	private $session;

	/** @var Session */
	private $userSession;

	/** @var IRequest */
	private $request;

	/**
	 * OAuth2 constructor.
	 *
	 * @param string $principalPrefix
	 */
	public function __construct(ISession $session,
								Session $userSession,
								IRequest $request,
								$principalPrefix = 'principals/users/') {
		$this->session = $session;
		$this->userSession = $userSession;
		$this->request = $request;
		$this->principalPrefix = $principalPrefix;

		// setup realm
		$defaults = new \OC_Defaults();
		$this->realm = $defaults->getName();
	}

	/**
	 * Whether the user has initially authenticated via DAV
	 *
	 * This is required for WebDAV clients that resent the cookies even when the
	 * account was changed.
	 *
	 * @see https://github.com/owncloud/core/issues/13245
	 *
	 * @param string $username
	 * @return bool
	 */
	public function isDavAuthenticated($username) {
		return !is_null($this->session->get(self::DAV_AUTHENTICATED)) &&
			$this->session->get(self::DAV_AUTHENTICATED) === $username;
	}

	/**
	 * Validates a Bearer token
	 *
	 * This method should return the full principal url, or false if the
	 * token was incorrect.
	 *
	 * @param string $bearerToken
	 * @return string|false
	 */
	protected function validateBearerToken($bearerToken) {
		if ($this->userSession->isLoggedIn() &&
			$this->isDavAuthenticated($this->userSession->getUser()->getUID())
		) {
			$userId = $this->userSession->getUser()->getUID();
			\OC_Util::setupFS($userId);
			$this->session->close();
			return $this->principalPrefix . $userId;
		} else {
			\OC_Util::setupFS(); //login hooks may need early access to the filesystem

			if ($this->userSession->tryAuthModuleLogin($this->request)) {
				$userId = $this->userSession->getUser()->getUID();
				\OC_Util::setupFS($userId);
				$this->session->set(self::DAV_AUTHENTICATED, $userId);
				$this->session->close();
				return $this->principalPrefix . $userId;
			} else {
				$this->session->close();
				return false;
			}
		}
	}

}
