<?php
/**
 * ownCloud - oauth2
 *
 * This file is licensed under the Affero General Public License version 3 or
 * later. See the COPYING file.
 *
 * @author Jonathan Neugebauer
 * @copyright Jonathan Neugebauer 2016
 */

use OCA\OAuth2\Db\ClientMapper;
use OCA\OAuth2\Db\AccessTokenMapper;
use OCP\AppFramework\App;

$tmpl = new OCP\Template('oauth2', 'settings-personal');

$app = new App('oauth2');
$container = $app->getContainer();
$clientMapper = new ClientMapper($container->query('ServerContainer')->getDb());
$accessTokenMapper = new AccessTokenMapper($container->query('ServerContainer')->getDb());

// TODO filter clients for authorized clients for the current user

return $tmpl->fetchPage(['clients' => $clientMapper->findAll()]);
