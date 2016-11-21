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
<h2><?php p($l->t('Authentifizierung aufrufen')); ?></h2>
<p><?php p($l->t('Insert the credentials for the authentication.')); ?></p>
<form action="authorize" method="get">
	<label for="response_type"><?php p($l->t( 'Response type' )); ?></label>
	<input type="text" name='response_type' id="response_type" placeholder="type"
		   value='<?php p($_['response_type']) ?>' />
	<br />
	<label for="client_id"><?php p($l->t( 'Client ID' )); ?></label>
	<input type="text" name='client_id' id="client_id" placeholder="<?php p($l->t('client'))?>"
		   value='<?php p($_['client_id']) ?>' />
	<br />
	<label for="redirect_uri"><?php p($l->t( 'URL' )); ?></label>
	<input type="text" name='redirect_uri' id="redirect_uri" placeholder="<?php p($l->t('URL'))?>"
		   value='<?php p($_['redirect_uri']) ?>' />
	<br />
	<input type="submit" value="Save"/>
</form>
