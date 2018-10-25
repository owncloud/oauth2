<?php
/**
 * @author Project Seminar "sciebo@Learnweb" of the University of Muenster
 * @copyright Copyright (c) 2017, University of Muenster
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

/** @var \OCA\OAuth2\Db\Client $client */
script('oauth2', 'settings');
style('oauth2', 'main');
style('oauth2', 'settings-admin');
?>

<div class="section" id="oauth2">
	<h2 class="app-name"><?php p($l->t('OAuth 2.0')); ?></h2>

    <h3><?php p($l->t('Registered clients')); ?></h3>
    <?php if (empty($_['clients'])) {
	p($l->t('No clients registered.'));
} else {
	?>
    <table class="grid">
        <thead>
        <tr>
            <th id="headerName" scope="col"><?php p($l->t('Name')); ?></th>
            <th id="headerRedirectUri" scope="col"><?php p($l->t('Redirection URI')); ?></th>
            <th id="headerClientIdentifier" scope="col"><?php p($l->t('Client Identifier')); ?></th>
            <th id="headerSecret" scope="col"><?php p($l->t('Secret')); ?></th>
			<th id="headerSubdomains" scope="col"><?php p($l->t('Subdomains allowed')); ?></th>
            <th id="headerRemove">&nbsp;</th>
        </tr>
        </thead>
        <tbody>
            <?php foreach ($_['clients'] as $client) {
		?>
                <tr>
                    <td><?php p($client->getName()); ?></td>
                    <td><?php p($client->getRedirectUri()); ?></td>
                    <td><code><?php p($client->getIdentifier()); ?></code></td>
                    <td><code><?php p($client->getSecret()); ?></code></td>
					<td id="td-allow-subdomains"><?php if ($client->getAllowSubdomains()) {
			?> <img alt="" src="/core/img/actions/checkmark.svg"> <?php
		} ?></td>
                    <td>
                        <form id="form-inline" class="delete" data-confirm="<?php p($l->t('Are you sure you want to delete this item?')); ?>" action="<?php p($_['urlGenerator']->linkToRoute('oauth2.settings.deleteClient', ['id' => $client->getId()])); ?>" method="post">
							<input type="hidden" name="requesttoken" value="<?php p($_['requesttoken']) ?>" />
                            <input type="submit" class="button icon-delete" value="">
                        </form>
                    </td>
                </tr>
            <?php
	} ?>
        </tbody>
    </table>
    <?php
} ?>

    <h3><?php p($l->t('Add client')); ?></h3>
    <form action="<?php p($_['urlGenerator']->linkToRoute('oauth2.settings.addClient')); ?>" method="post">
		<input id="name" name="name" type="text" placeholder="<?php p($l->t('Name')); ?>">
        <input id="redirect_uri" name="redirect_uri" type="text" placeholder="<?php p($l->t('Redirection URI')); ?>">
		<input type="checkbox" class="checkbox" name="allow_subdomains" id="allow_subdomains" value="1"/>
		<label for="allow_subdomains"><?php p($l->t('Allow subdomains'));?></label>
		<input type="hidden" name="requesttoken" value="<?php p($_['requesttoken']) ?>" />
        <input type="submit" class="button" value="<?php p($l->t('Add')); ?>">
    </form>
</div>
