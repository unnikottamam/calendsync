<?php
if (!defined('sugarEntry') || !sugarEntry) {
    die('Not A Valid Entry Point');
}

require_once('include/MVC/View/SugarView.php');

class CalendsyncViewEdit extends SugarView
{
    public function display()
    {
        $clientId = "";
        $clientSecret = "";
        $refreshToken = "";
        $query = "SELECT * FROM calendsync_tokens ORDER BY id DESC LIMIT 1";
        $result = $GLOBALS['db']->query($query);
        $row = $GLOBALS['db']->fetchByAssoc($result);
        if ($row) {
            $clientId = $row['client_id'];
            $clientSecret = $row['client_secret'];
            $refreshToken = $row['refresh_token'];
        }

        $formString = <<<FORM
            <form method="post" action="index.php?module=calendsync&action=saveTokens">
                <h2 style="margin: 0 0 5px;">Calendly API Configuration</h2>
                <p style="margin: 0">Enter your Calendly API Information</p>
                <div>
                    <input type="hidden" name="module" value="calendsync">
                        <input required type="hidden" name="action" value="saveTokens">
                    <div style="display: flex; flex-direction: column; gap: 5px; max-width: 300px; margin-top: 10px;">
                        <label style="display: block; margin: 0;" for="client_id">Client ID</label>
                        <input required type="text" name="client_id" placeholder="Client ID" value="$clientId">
                    </div>
                    <div style="display: flex; flex-direction: column; gap: 5px; max-width: 300px; margin-top: 10px;">
                        <label style="display: block; margin: 0;" for="client_secret">Client Secret</label>
                        <input required type="text" name="client_secret" placeholder="Client Secret" value="$clientSecret">
                    </div>
                    <div style="display: flex; flex-direction: column; gap: 5px; max-width: 300px; margin-top: 10px;">
                        <label style="display: block; margin: 0;" for="refresh_token">Refresh Token</label>
                        <input required type="text" name="refresh_token" placeholder="Refresh Token" value="$refreshToken">
                    </div>
                    <input type="submit" value="Save" style="margin-top: 10px;">
                </div>
            </form>
        FORM;
        echo $formString;
        parent::display();
    }
}