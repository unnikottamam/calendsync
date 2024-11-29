<?php
if (!defined('sugarEntry') || !sugarEntry) {
    die('Not A Valid Entry Point');
}

require_once('include/MVC/Controller/SugarController.php');

class CalendsyncController extends SugarController
{
    public function __construct()
    {
        parent::__construct();
        $this->actions['saveTokens'] = 'saveTokens';
    }

    public function process()
    {
        if ($this->action === 'saveTokens') {
            $this->saveTokens();
        } else {
            parent::process();
        }
    }

    public function saveTokens()
    {
        $clientId = $_POST['client_id'];
        $clientSecret = $_POST['client_secret'];
        $redirectUri = $_POST['redirect_uri'];
        $authorizeCode = $_POST['authorize_code'];

        $query = "SELECT * FROM calendsync_tokens ORDER BY id DESC LIMIT 1";
        $result = $GLOBALS['db']->query($query);
        $row = $GLOBALS['db']->fetchByAssoc($result);
        if ($row) {
            $check = ($row['client_id'] === $clientId) &&
                ($row['client_secret'] === $clientSecret) &&
                ($row['redirect_uri'] === $redirectUri) &&
                ($row['authorize_code'] === $authorizeCode);
            if (!$row['refresh_token'] || !$row['access_token'] || !$row['organization'] || !$row['owner'] || !$check) {
                $tokens = $this->generateAccessRefreshTokens($clientId, $clientSecret, $redirectUri, $authorizeCode);
                $accessToken = $tokens[0];
                $refreshToken = $tokens[1];
                $organization = $tokens[2];
                $owner = $tokens[3];
            } else {
                $accessToken = $row['access_token'];
                $refreshToken = $row['refresh_token'];
                $organization = $row['organization'];
                $owner = $row['owner'];
            }

            $sql = "UPDATE calendsync_tokens
                    SET client_id = '$clientId',
                        client_secret = '$clientSecret',
                        redirect_uri = '$redirectUri',
                        owner = '$owner',
                        authorize_code = '$authorizeCode',
                        refresh_token = '$refreshToken',
                        access_token = '$accessToken',
                        organization = '$organization',
                        expires_at = NOW()
                    WHERE id = " . $row['id'];
        } else {
            $tokens = $this->generateAccessRefreshTokens($clientId, $clientSecret, $redirectUri, $authorizeCode);
            $accessToken = $tokens[0];
            $refreshToken = $tokens[1];
            $organization = $tokens[2];
            $owner = $tokens[3];
            $sql = "INSERT INTO calendsync_tokens (client_id, client_secret, redirect_uri, authorize_code, owner, refresh_token, access_token, organization, expires_at)
                    VALUES ('$clientId', '$clientSecret', '$redirectUri', '$authorizeCode', '$owner', '$refreshToken', '$accessToken', '$organization', NOW())";
        }
        $GLOBALS['db']->query($sql);

        header("Location: index.php?module=calendsync&action=EditView");
        exit();
    }

    private function generateAccessRefreshTokens($clientId, $clientSecret, $redirectUri, $authorizeCode): array
    {
        $url = 'https://auth.calendly.com/oauth/token';
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query([
            'grant_type' => 'authorization_code',
            'client_id' => $clientId,
            'client_secret' => $clientSecret,
            'redirect_uri' => $redirectUri,
            'code' => $authorizeCode
        ]));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $result = curl_exec($ch);
        curl_close($ch);
        $response = json_decode($result, true);
        $accessToken = $response['access_token'];
        $refreshToken = $response['refresh_token'];
        $organization = $response['organization'];
        $owner = $response['owner'];
        $this->createWebhook($accessToken, $organization, $owner);
        return [$accessToken, $refreshToken, $organization, $owner];
    }

    private function createWebhook($accessToken, $organization, $owner)
    {
        $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' ? "https" : "http";
        $thisWebsiteUrl = "$protocol://" . $_SERVER['HTTP_HOST'];
        $webHookUrl = $thisWebsiteUrl . '/index.php?entryPoint=CalendSync';

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, "https://api.calendly.com/webhook_subscriptions?organization=$organization");
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'GET');
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            "Content-type: application/json",
            "Authorization: Bearer $accessToken"
        ]);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $result = curl_exec($ch);
        curl_close($ch);
        $webhookSubscriptions = json_decode($result, true);
        if (!empty($webhookSubscriptions['collection'])) {
            foreach ($webhookSubscriptions['collection'] as $subscription) {
                if ($subscription['callback_url'] === $webHookUrl) {
                    return true;
                }
            }
        }

        $data = [
            'url' => $webHookUrl,
            'events' => [
                'invitee.created',
                'invitee.canceled',
                'invitee_no_show.created',
                'invitee_no_show.deleted'
            ],
            'organization' => $organization,
            'user' => $owner,
            'scope' => 'organization'
        ];
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, 'https://api.calendly.com/webhook_subscriptions');
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            "Content-type: application/json",
            "Authorization: Bearer $accessToken"
        ]);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $result = curl_exec($ch);
        curl_close($ch);
        return json_decode($result, true);
    }
}