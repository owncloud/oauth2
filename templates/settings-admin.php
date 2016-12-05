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
?>
<div class="section" id="oauth2">
    <h2><?php p($l->t('OAuth 2.0')); ?></h2>

    <h3><?php p($l->t('Registered clients')); ?></h3>
    <table class="grid">
        <thead>
        <tr>
            <th id="headerName" scope="col"><?php p($l->t('Name')); ?></th>
            <th id="headerRedirectUri" scope="col"><?php p($l->t('Redirect URI')); ?></th>
            <th id="headerClientId" scope="col"><?php p($l->t('Client ID')); ?></th>
            <th id="headerSecret" scope="col"><?php p($l->t('Secret')); ?></th>
            <th id="headerRemove">&nbsp;</th>
        </tr>
        </thead>
        <tbody>
            <?php foreach ($_['clients'] as $client) { ?>
                <tr>
                    <td><?php p($client->getName())?></td>
                    <td><?php p($client->getRedirectUri())?></td>
                    <td><?php p($client->getId())?></td>
                    <td><?php p($client->getSecret())?></td>
                    <td>
                        <form action="../apps/oauth2/clients/<?php p($client->getId())?>/delete" method="post"
                              style='display:inline;'>
                            <input type="submit" class="button icon-delete" value="">
                        </form>
                    </td>
                </tr>
            <?php } ?>
        </tbody>
    </table>

    <h3><?php p($l->t('Add client')); ?></h3>
    <form action="../apps/oauth2/clients" method="post">
        <input id="name" name="name" type="text" placeholder="<?php p($l->t
        ('Name')); ?>">
        <input id="redirect_uri" name="redirect_uri" type="url"
               placeholder="<?php p($l->t('Redirect URI')); ?>">
        <input type="submit" class="button" value="<?php p($l->t('Add')); ?>">
    </form>
</div>
