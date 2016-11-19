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
	<p><?php p($l->t('Insert the credentials for the authentication.')); ?></p>
	<form action="SettingsController.php" method="post">
		<label for="AUTH_USER"><?php p($l->t( 'User' )); ?></label>
		<input type="text" name='AUTH_USER' id="AUTH_USER" placeholder="User"
			   value='<?php p($_['PHP_AUTH_USER']) ?>' />
		<br />
		<label for="AUTH_SECRET"><?php p($l->t( 'Password' )); ?></label>
		<input type="password" name='AUTH_SECRET' id="AUTH_SECRET" placeholder="Password"
			   value='<?php p($_['PHP_AUTH_SECRET']) ?>' />
		<br />
		<input type="submit" name="submitCredentials" id="submitCredentials"
		   value="<?php p($l->t( 'Save' )); ?>"/>
	</form>
</div>
