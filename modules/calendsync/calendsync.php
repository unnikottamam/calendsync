<?php
if (!defined('sugarEntry') || !sugarEntry) {
    die('Not A Valid Entry Point');
}

require_once("modules/calendsync/CalendlyAPI.php");
$suiteCalendlyUtils = new CalendlyAPI();

$inputJSON = file_get_contents('php://input');
$input = json_decode($inputJSON, TRUE);

if ($input['event'] === "invitee.created") {
    $questionsAnswers = "";
    foreach ($input['payload']['questions_and_answers'] as $key => $value) {
        $questionsAnswers .= "Question" . ++$key . " - " . $value['question'] . "\n" . "Answer -" . $value['answer'] . "\n";
    }

    $getEmailDetails = $suiteCalendlyUtils->getEmailDetails($input['payload']['email']);
    $beanId = $getEmailDetails['bean_id'] ?? "";
    $beanModule = $getEmailDetails['bean_module'] ?? "";

    if (!$beanId) {
        $createContact = BeanFactory::newBean("Contacts");
        $createContact->last_name = empty($input['payload']['last_name']) ? $input['payload']['name'] : $input['payload']['last_name'];
        $createContact->email1 = $input['payload']['email'];
        $createContact->assigned_user_id = "1";
        $createContact->modified_user_id = "1";
        $createContact->created_by = "1";
        $createContact->update_date_modified = false;
        $createContact->update_modified_by = false;
        $createContact->set_created_by = false;
        $createContact->update_date_entered = false;
        $beanId = $createContact->save();
        $beanModule = "Contacts";
    }

    $eventInfo = explode("/", $input['payload']['event']);
    $eventUUID = end($eventInfo);
    $scheduledEvent = $input['payload']['scheduled_event'];
    $eventDetailsFromPayload = [
        "start_time" => gmdate('Y-m-d H:i:s', strtotime($scheduledEvent['start_time'])),
        "end_time" => gmdate('Y-m-d H:i:s', strtotime($scheduledEvent['end_time'])),
        "name" => $scheduledEvent['name']
    ];
    $owner = "1";
    if (!empty($scheduledEvent['event_memberships']) && !empty($scheduledEvent['event_memberships']['user'])) {
        $ownerInfo = explode("/", $scheduledEvent['event_memberships']['user']);
        $owner = end($ownerInfo);
    }

    if (!empty($input['payload']['uri'])) {
        $createMeeting = BeanFactory::newBean("Meetings");
        $createMeeting->name = "Scheduled Meeting with " . $input['payload']['name'];
        $createMeeting->calendsync_event_name = $eventDetailsFromPayload['name'];
        $createMeeting->calendsync_email = $input['payload']['email'];
        $createMeeting->calendsync_uuid = $eventUUID;
        $createMeeting->description = $questionsAnswers;
        $createMeeting->date_start = $eventDetailsFromPayload['start_time'];
        $createMeeting->date_end = $eventDetailsFromPayload['end_time'];
        $createMeeting->parent_type = $beanModule;
        $createMeeting->parent_id = $beanId;
        $createMeeting->status = "Planned";
        $createMeeting->assigned_user_id = $owner;
        $createMeeting->modified_user_id = $owner;
        $createMeeting->created_by = $owner;
        $createMeeting->update_date_modified = false;
        $createMeeting->update_modified_by = false;
        $createMeeting->set_created_by = false;
        $createMeeting->update_date_entered = false;
        $meetId = $createMeeting->save();

        //Relating Contact and Meeting
        if ($beanId & $meetId) {
            $modulesList = array("Contacts", "Leads", "Accounts");
            if (in_array($beanModule, $modulesList)) {
                $Relationship = BeanFactory::getBean($beanModule, $beanId);
                $Relationship->load_relationship('meetings');
                $Relationship->meetings->add($meetId);
            }
        }

        $currentGMDatetime = gmdate("Y-m-d H:i:s");
        $GLOBALS['db']->query("INSERT INTO meetings_users (id,meeting_id,user_id,required,accept_status,date_modified) VALUES (UUID(), '$meetId', '1', '1', 'accept', '$currentGMDatetime')");
    }
}

if ($input['event'] === "invitee.canceled") {
    $eventInfo = explode("/", trim($input['payload']['event']));
    $eventUUID = end($eventInfo);
    $inviteeEmail = trim($input['payload']['email']);
    $reason = trim($input['payload']['cancellation']['reason']);
    $suiteCalendlyUtils->cancelMeeting($eventUUID, $inviteeEmail, $reason);
}