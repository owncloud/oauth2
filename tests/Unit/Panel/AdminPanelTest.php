<?php

namespace OCA\OAuth2\Tests\Unit\Panel;

use OCA\OAuth2\Db\ClientMapper;
use OCA\OAuth2\Panels\AdminPanel;
use PHPUnit\Framework\TestCase;

class AdminPanelTest extends TestCase {
	/** @var AdminPanel */
	private $panel;

	/** @var ClientMapper */
	private $clientMapper;

	protected function setUp() {
		parent::setUp();
		$this->clientMapper = $this->createMock(ClientMapper::class);
		$this->panel = new AdminPanel($this->clientMapper);
	}

	public function testPanel() {
		$this->clientMapper->method('findAll')->willReturn([]);
		$page = $this->panel->getPanel()->fetchPage();
		$this->assertContains('No clients registered.', $page);
	}
}
