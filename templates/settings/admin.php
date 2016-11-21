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
        <tr>
            <td><?php var_dump($_['clients'])?></td>
            <td>https://learnweb.de/cb</td>
            <td>12oindni2o2no1</td>
            <td>12io23bnfipo2poemncnwipe</td>
            <td><a class="action delete" href="#"><img class="svg action" src="/core/img/actions/delete.svg"></a></td>
        </tr>
        </tbody>
    </table>

    <h3><?php p($l->t('Add client')); ?></h3>
    <form action="../apps/oauth2/clients" method="post">
        <input id="name" name="name" type="text" placeholder="<?php p($l->t('Name')); ?>">
        <input id="redirect_uri" name="redirect_uri" type="url" placeholder="<?php p($l->t('Redirect URI')); ?>">
        <input type="submit" class="button" value="<?php p($l->t('Add')); ?>">
    </form>
</div>
