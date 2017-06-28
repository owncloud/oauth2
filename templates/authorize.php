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

style('oauth2', 'main');
style('oauth2', 'authorize');
?>

<div id="app">
    <div id="app-content">
        <div id="app-content-wrapper">
            <div id="authorize-dialog">
				<p><b><?php p($l->t('The “%s“ application would like permission to access your account', [$_['client_name']]));?></b></p>
				<p><?php p($l->t('The application will gain access to your username and will be allowed to manage files, folders and shares.')); ?></p>
                <form id="form-inline" action="" method="post">
                    <button type="submit"><?php p($l->t('Authorize')); ?></button>
                </form>
                <a href="../../?cancel">
                    <button><?php p($l->t('Cancel')); ?></button>
                </a>
            </div>
        </div>
    </div>
</div>
