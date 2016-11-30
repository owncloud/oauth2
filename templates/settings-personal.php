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
?>
<div class="section" id="oauth2">
    <h2><?php p($l->t('OAuth 2.0')); ?></h2>

    <h3><?php p($l->t('Authorized Applications')); ?></h3>
	<table class="grid">
		<thead>
		<tr>
			<th id="headerName" scope="col"><?php p($l->t('Name')); ?></th>
			<th id="headerRemove" width="45px">&nbsp;</th>
		</tr>
		</thead>
		<tbody>
		<?php foreach ($_['clients'] as $client) { ?>
			<tr>
				<td><?php p($client->getName())?></td>
				<td>
					<form action="#" method="post"
						  style='display:inline;'>
						<input type="submit" class="button icon-delete" value="">
					</form>
				</td>
			</tr>
		<?php } ?>
		</tbody>
	</table>
</div>
