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

use OCA\Oauth2\AppInfo\Application;

OCP\User::checkAdminUser();

$app = new Application('oauth2');
$clientMapper = new \OCA\OAuth2\Db\ClientMapper($app->getDatabaseConnection());

$tmpl = new OCP\Template('oauth2', 'settings/admin');

return $tmpl->fetchPage(['clients' => $clientMapper->findAll()]);
