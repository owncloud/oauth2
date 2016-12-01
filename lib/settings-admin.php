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

use OCA\OAuth2\Db\ClientMapper;
use OCP\AppFramework\App;

OCP\User::checkAdminUser();

$app = new App('oauth2');
$container = $app->getContainer();
$clientMapper = new ClientMapper($container->query('ServerContainer')->getDb());

$tmpl = new OCP\Template('oauth2', 'settings-admin');

$clients = $clientMapper->findAll();

return $tmpl->fetchPage(['clients' => $clientMapper->findAll()]);
