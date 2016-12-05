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
<div id="app">
	<div id="app-content">
		<div id="app-content-wrapper">
			<div
				style="text-align: center; position: absolute; top: 50%; left: 50%; transform: translateX(-50%) translateY(-50%);">
				<p><b>Do you really like to authorize the client "<?php p($_['client_name']) ?>"?</b></p>

				<form action="" method="post" name="form">
					<button type="submit">Authorize</button>
				</form>
				<a href="../../">
					<button>Cancel</button>
				</a>
			</div>
		</div>
	</div>
</div>
