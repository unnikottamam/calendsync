<?php
if (!defined('sugarEntry') || !sugarEntry) {
    die('Not A Valid Entry Point');
}

class CalendlyAPI
{
    private string $clientId;
    private string $clientSecret;
    private string $accessToken;
    private string $refreshToken;
    private string $calendlyUser;
    private $tokenExpirationTime;

    public function __construct()
    {
        $query = "SELECT * FROM calendsync_tokens LIMIT 1";
        $result = $GLOBALS['db']->query($query);
        $row = $GLOBALS['db']->fetchByAssoc($result);
        $this->clientId = $row['client_id'];
        $this->clientSecret = $row['client_secret'];
        $this->accessToken = $row['access_token'];
        $this->refreshToken = $row['refresh_token'];
        $this->calendlyUser = $row['owner'];
        $this->tokenExpirationTime = strtotime($row['expires_at']);
    }

    public function getEmailDetails($emailAddress): array
    {
        if (!empty($emailAddress)) {
            $sql = "SELECT bean_id, bean_module FROM email_addr_bean_rel
                    WHERE email_address_id = (
                        SELECT id FROM email_addresses
                        WHERE email_address = '" . $emailAddress . "'
                            AND deleted =0)
                        AND deleted=0
                    ORDER BY date_modified DESC
                    LIMIT 1";
            $result = $GLOBALS['db']->query($sql);
            $details = $GLOBALS['db']->fetchByAssoc($result);
            $beanId = $details & isset($details['bean_id']) ? $details['bean_id'] : "";
            if (!$beanId) {
                return [];
            }
            $bean_module = $details['bean_module'];
            return [
                "bean_id" => $beanId,
                "bean_module" => $bean_module
            ];
        } else {
            return [];
        }
    }

    public function getEventDetails($event): array
    {
        $this->ensureTokenValidity();
        $url = 'https://api.calendly.com/scheduled_events/' . $event;
        $headers = [
            'Authorization: Bearer ' . $this->accessToken,
            'Content-Type: application/json'
        ];

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'GET');
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

        $response = curl_exec($ch);
        if (curl_errno($ch)) {
            echo 'Error:' . curl_error($ch);
        }
        curl_close($ch);

        $responseData = json_decode($response, true);
        $startTime = gmdate('Y-m-d H:i:s', strtotime($responseData['resource']['start_time']));
        $endTime = gmdate('Y-m-d H:i:s', strtotime($responseData['resource']['end_time']));
        $eventName = $responseData['resource']['name'];
        return [
            "start_time" => $startTime,
            "end_time" => $endTime,
            "name" => $eventName,
            "owner" => $this->calendlyUser
        ];
    }

    public function cancelMeeting(string $eventId, string $inviteeEmail, string $cancelReason = "")
    {
        if ($eventId && $inviteeEmail) {
            $getMeeting = "SELECT id FROM `meetings` WHERE `calendsync_uuid` LIKE '" . $eventId . "'";
            $getMeetingResult = $GLOBALS['db']->query($getMeeting);
            $details = $GLOBALS['db']->fetchByAssoc($getMeetingResult);
            $meetingId = $details && isset($details['id']) ? $details['id'] : "";
            if ($meetingId) {
                $getMeeting = BeanFactory::getBean("Meetings", $meetingId);
                $getMeeting->calendsync_cancel_reason = $cancelReason;
                $getMeeting->status = "Not Held";
                $getMeeting->save();
            }
        }
    }

    public function noShowMeeting(string $eventId, string $inviteeEmail)
    {
        if ($eventId && $inviteeEmail) {
            $getMeeting = "SELECT id FROM `meetings` WHERE `calendsync_uuid` LIKE '" . $eventId . "'";
            $getMeetingResult = $GLOBALS['db']->query($getMeeting);
            $details = $GLOBALS['db']->fetchByAssoc($getMeetingResult);
            $meetingId = $details && isset($details['id']) ? $details['id'] : "";
            if ($meetingId) {
                $getMeeting = BeanFactory::getBean("Meetings", $meetingId);
                $getMeeting->calendsync_no_show = "Yes";
            }
        }
    }

    private function ensureTokenValidity()
    {
        if (time() >= $this->tokenExpirationTime) {
            $this->getNewAccessToken();
        }
    }

    public function getNewAccessToken()
    {
        $url = 'https://auth.calendly.com/oauth/token';
        $headers = [
            'Content-Type: application/x-www-form-urlencoded'
        ];
        $data = http_build_query([
            'client_id' => $this->clientId,
            'client_secret' => $this->clientSecret,
            'grant_type' => 'refresh_token',
            'refresh_token' => $this->refreshToken
        ]);
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $response = curl_exec($ch);
        curl_close($ch);
        $responseData = json_decode($response, true);
        $this->accessToken = $responseData['access_token'];
        $this->refreshToken = $responseData['refresh_token'];
        $owner = explode('/', $responseData['owner']);
        $this->calendlyUser = end($owner);
        $this->tokenExpirationTime = time() + $responseData['expires_in'];

        $sql = "UPDATE calendsync_tokens
                SET access_token = '" . $this->accessToken . "',
                    refresh_token = '" . $this->refreshToken . "',
                    owner = '" . $this->calendlyUser . "',
                    expires_at = '" . date('Y-m-d H:i:s', $this->tokenExpirationTime) . "'";
        $GLOBALS['db']->query($sql);
    }
}
