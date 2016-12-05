<div id="app">
    <div id="app-content">
        <div id="app-content-wrapper";
             style="position: relative;">
            <div
                style="text-align: center; position: absolute; top: 40%; left: 50%; transform: translateX(-50%) translateY(-50%);
                border:1px solid #1e2d43; background-color:#f8f8f8; padding: 10px; margin: 15px;background-color:#f8f8f8">
                <p><b>Do you really like to authorize the application "<?php p($_['client_name']) ?>"?</b></p>
                <p><b>The application will gain access to your files and username and is allowed to generate folders for collaborative sharing.</b></p>
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