<div id="app">
	<div id="app-content">
		<div id="app-content-wrapper">
			<div style="text-align: center; position: absolute; top: 50%; left: 50%; transform: translateX(-50%) translateY(-50%);">
				<p><b>Do you really like to authorize the client with identifier "<?php p($_['client_id'])?>"?</b></p>

				<form action="" method="post" name="form">
					<button type="submit">Authorize</button>
				</form>
				<a href="../../"><button>Cancel</button></a>
			</div>
		</div>
	</div>
</div>