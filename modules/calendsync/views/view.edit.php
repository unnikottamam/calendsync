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
        $redirectUri = "";
        $authorizeCode = "";
        $query = "SELECT * FROM calendsync_tokens ORDER BY id DESC LIMIT 1";
        $result = $GLOBALS['db']->query($query);
        $row = $GLOBALS['db']->fetchByAssoc($result);

        $organization = "";
        $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' ? "https" : "http";
        $thisWebsiteUrl = "$protocol://" . $_SERVER['HTTP_HOST'];
        $owner = "";
        if ($row) {
            $clientId = $row['client_id'];
            $clientSecret = $row['client_secret'];
            $redirectUri = $row['redirect_uri'];
            $authorizeCode = $row['authorize_code'];
            $organization = $row['organization'];
            $owner = $row['owner'];
        }

        $pageBottomContent = "";
        if ($organization && $owner) {
            $pageBottomContent = <<<HTML
                <div style="margin-top: 20px;">
                    <hr style="border-top: 1px solid #ccc;" />
                    <p>Organization and Owner information are auto generated after saving the Calendly API Configuration.</p>
                    <div style="display: flex; flex-direction: column; gap: 5px; max-width: 300px; margin-top: 10px;">
                        <label style="display: block; margin: 0;" for="organization">Organization</label>
                        <input type="text" name="organization" placeholder="Organization" value="$organization" disabled>
                    </div>
                    <div style="display: flex; flex-direction: column; gap: 5px; max-width: 300px; margin-top: 10px;">
                        <label style="display: block; margin: 0;" for="owner">Owner</label>
                        <input type="text" name="owner" placeholder="Owner" value="$owner" disabled>
                    </div>
                </div>
            HTML;
        }

        $paraContent = "Enter your Calendly API Information";
        if ($organization && $owner) {
            $paraContent = "You have already saved the Calendly API configuration; change it only if needed.";
        }
        $stepsForAPIKeys = <<<HTML
            <h2 style="margin: 0 0 5px;">Steps to get Calendly API Keys</h2>
            <ol style="list-style: inside;">
                <li>Go to <a href="https://developer.calendly.com/" target="_blank">Calendly Developer Portal</a></li>
                <li>Create account or login</li>
                <li>Click on <strong>Create New App</strong></li>
                <li>Fill in the details</li>
                <li>Provide <strong>Redirect URI</strong>, e.g., <code>$thisWebsiteUrl</code></li>
                <li>Copy <strong>Client ID</strong>, <strong>Client Secret</strong> and <strong>Redirect URI</strong> here</li>
            </ol>
            <h3 style="font-size: 20px;">Steps to get Calendly Authorize Code</h3>
            <ol style="list-style: inside;">
                <li>Using the <strong>Client ID</strong> and <strong>Redirect URI</strong>, create a URL in your browser like this:</li>
                <code>https://auth.calendly.com/oauth/authorize?client_id=YOUR_CLIENT_ID&response_type=code&redirect_uri=YOUR_REDIRECT_URI</code>
                <li>Replace <code>YOUR_CLIENT_ID</code> and <code>YOUR_REDIRECT_URI</code> with your <strong>Client ID</strong> and <strong>Redirect URI</strong></li>
                <li>Visit the URL in your browser</li>
                <li>Authorize the app</li>
                <li>Copy the <strong>Code</strong> from the URL, eg., <code>$thisWebsiteUrl?code=f04281d639d8248435378b0365de7bd1f53bf452eda187d5f1e07ae7f04546d6</code></li>
                <li>Paste the <strong>Code</strong> here in the <strong>Authorize Code</strong> field</li>
            </ol>
        HTML;

        $formString = <<<FORM
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px;">
                <form method="post" action="index.php?module=calendsync&action=saveTokens">
                    <h2 style="margin: 0 0 5px;">Calendly API Configuration</h2>
                    <p style="margin: 0">$paraContent</p>
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
                        <label style="display: block; margin: 0;" for="redirect_uri">Redirect URI</label>
                        <input required type="text" name="redirect_uri" placeholder="Redirect URI" value="$redirectUri">
                    </div>
                    <div style="display: flex; flex-direction: column; gap: 5px; max-width: 300px; margin-top: 10px;">
                        <label style="display: block; margin: 0;" for="authorize_code">Authorize Code</label>
                        <input required type="text" name="authorize_code" placeholder="Authorize Code" value="$authorizeCode">
                    </div>
                    <input type="submit" value="Save" style="margin-top: 10px;">
                    $pageBottomContent
                </form>
                <div>$stepsForAPIKeys</div>
            </div>
        FORM;
        echo $formString;
        parent::display();
    }
}