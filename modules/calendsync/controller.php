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
        $refreshToken = $_POST['refresh_token'];

        $query = "SELECT * FROM calendsync_tokens ORDER BY id DESC LIMIT 1";
        $result = $GLOBALS['db']->query($query);
        $row = $GLOBALS['db']->fetchByAssoc($result);
        if ($row) {
            $sql = "UPDATE calendsync_tokens
                    SET client_id = '$clientId',
                        client_secret = '$clientSecret',
                        refresh_token = '$refreshToken',
                        access_token = '',
                        owner = '',
                        expires_at = NOW()
                    WHERE id = " . $row['id'];
        } else {
            $sql = "INSERT INTO calendsync_tokens (client_id, client_secret, refresh_token, owner, access_token, expires_at)
                    VALUES ('$clientId', '$clientSecret', '$refreshToken', '', '', NOW())";
        }
        $GLOBALS['db']->query($sql);

        header("Location: index.php?module=calendsync&action=EditView");
        exit();
    }
}