<?php
/**
 * @author Thomas Müller <thomas.mueller@tmit.eu>
 * @copyright Copyright (c) 2018, ownCloud GmbH
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

namespace OCA\OAuth2\Tests\Unit\Controller;


use OCA\OAuth2\Controller\OpenIdConnectController;
use OCP\AppFramework\Http\JSONResponse;
use OCP\IAvatar;
use OCP\IAvatarManager;
use OCP\IRequest;
use OCP\IURLGenerator;
use OCP\IUser;
use OCP\IUserSession;

class OpenIdConnectControllerTest extends \PHPUnit_Framework_TestCase {

	/** @var OpenIdConnectController */
	private $controller;
	/** @var IRequest | \PHPUnit_Framework_MockObject_MockObject */
	private $request;
	/** @var IUserSession | \PHPUnit_Framework_MockObject_MockObject */
	private $userSession;
	/** @var IURLGenerator | \PHPUnit_Framework_MockObject_MockObject */
	private $urlGenerator;
	/** @var IAvatarManager | \PHPUnit_Framework_MockObject_MockObject */
	private $avatarManager;

	public function setUp() {
		parent::setUp();
		$this->request = $this->createMock(IRequest::class);
		$this->userSession = $this->createMock(IUserSession::class);
		$this->urlGenerator = $this->createMock(IURLGenerator::class);
		$this->avatarManager = $this->createMock(IAvatarManager::class);

		$this->urlGenerator->method('linkTo')->willReturn('/remote.php');
		$this->urlGenerator->method('getAbsoluteURL')->willReturnCallback(function($url) {
			return "https://cloud.nasa.gov$url";
		});

		$this->controller = new OpenIdConnectController('oauth2',
			$this->request, $this->userSession, $this->urlGenerator, $this->avatarManager);
	}

	/**
	 * @dataProvider providesUser
	 * @param IUser $user
	 * @param IAvatar $avatar
	 * @param $expectedData
	 * @throws \Exception
	 */
	public function testUserInfoRoute(IUser $user, IAvatar $avatar, $expectedData) {
		$this->userSession->method('getUser')->willReturn($user);
		$this->avatarManager->method('getAvatar')->willReturn($avatar);

		$info = $this->controller->userInfo();
		$this->assertInstanceOf(JSONResponse::class, $info);
		$this->assertEquals($expectedData, $info->getData());
	}

	public function providesUser() {
		$user1 = $this->createUserMock();
		$user2 = $this->createUserMock('Scarlet Witch');
		$user3 = $this->createUserMock(null, 'foo@bar.net');

		$avatar1 = $this->createMock(IAvatar::class);
		$avatar1->method('exists')->willReturn(true);
		$avatar2 = $this->createMock(IAvatar::class);
		$avatar2->method('exists')->willReturn(false);

		return [
			[$user1, $avatar1, ['sub' => 'abcd-efgh-1234', 'picture' => 'https://cloud.nasa.gov/remote.php/dav/avatars/abcd-efgh-1234/96.jpeg']],
			[$user2, $avatar1, ['sub' => 'abcd-efgh-1234', 'picture' => 'https://cloud.nasa.gov/remote.php/dav/avatars/abcd-efgh-1234/96.jpeg', 'name' => 'Scarlet Witch']],
			[$user3, $avatar1, ['sub' => 'abcd-efgh-1234', 'picture' => 'https://cloud.nasa.gov/remote.php/dav/avatars/abcd-efgh-1234/96.jpeg', 'email' => 'foo@bar.net']],
			[$user1, $avatar2, ['sub' => 'abcd-efgh-1234']],
		];
	}

	/**
	 * @param null $displayName
	 * @param null $email
	 * @return \PHPUnit_Framework_MockObject_MockObject
	 */
	public function createUserMock($displayName = null, $email = null) {
		$user1 = $this->createMock(IUser::class);
		$user1->method('getUID')->willReturn('abcd-efgh-1234');
		if ($email !== null) {
			$user1->method('getEMailAddress')->willReturn($email);
		}
		if ($displayName !== null) {
			$user1->method('getDisplayName')->willReturn($displayName);
		}
		return $user1;
	}
}