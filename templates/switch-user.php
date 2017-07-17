<?php
/**
 * @author Thomas Müller <thomas.mueller@tmit.eu>
 *
 * @copyright Copyright (c) 2017, ownCloud GmbH
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

style('oauth2', 'authorization');
?>

<span class="error">
	<form id="form-inline" action="" method="post">
		<p><b><?php p($l->t('Switch user')); ?></b></p>
		<br>
		<p><?php p($l->t('You are logged as %s but the application requested access for user %s.', [$_['current_user'], $_['requested_user']])); ?></p>
		<br>
		<a href="<?php p($_['logout_url']); ?>">
			<button><?php p($l->t('Logout and login as %s', $_['requested_user'])); ?></button>
		</a>
	</form>
</span>
