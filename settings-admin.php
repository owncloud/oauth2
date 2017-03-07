<?php
/**
 * @author Lukas Biermann
 * @author Nina Herrmann
 * @author Wladislaw Iwanzow
 * @author Dennis Meis
 * @author Jonathan Neugebauer
 *
 * @copyright Copyright (c) 2017, Project Seminar "PSSL16" at the University of Muenster.
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

use OCA\OAuth2\AppInfo\Application;
use OCA\OAuth2\Db\ClientMapper;

OCP\User::checkAdminUser();

$app = new Application();
$container = $app->getContainer();

/** @var ClientMapper $clientMapper */
$clientMapper = $container->query('OCA\OAuth2\Db\ClientMapper');

$tmpl = new OCP\Template('oauth2', 'settings-admin');

$clients = $clientMapper->findAll();

return $tmpl->fetchPage(['clients' => $clientMapper->findAll()]);
