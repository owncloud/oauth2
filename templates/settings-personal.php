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
				<td><?php p($client->getName()); ?></td>
				<td>
					<form action="../apps/oauth2/clients/<?php p($client->getId()); ?>/revoke?user_id=<?php p($_['user_id']); ?>" method="post"
						  style='display:inline;'>
						<input type="submit" class="button icon-delete" value="">
					</form>
				</td>
			</tr>
		<?php } ?>
		</tbody>
	</table>
</div>
