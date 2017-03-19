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

style('oauth2', 'authorize');
?>

<div id="app">
	<div id="app-content">
		<div id="app-content-wrapper">
			<div id="authorize-dialog">
				<p><b><?php p($l->t('Request not valid')); ?></b></p>
				<?php if (is_null($_['client_name'])) { ?>
					<p><?php p($l->t('This request is not valid. Please contact the administrator if this error persists.')); ?></p>
				<?php } else { ?>
					<p><?php p($l->t('This request is not valid. Please contact the administrator of “')); ?><?php p($_['client_name']); ?><?php p($l->t('” if this error persists.')); ?></p>
				<?php } ?>
				<a href="<?php p($_['back_url']); ?>">
					<button><?php p($l->t('Back')); ?></button>
				</a>
			</div>
		</div>
	</div>
</div>
