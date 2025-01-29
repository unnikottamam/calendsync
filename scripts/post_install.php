<?php
if (!defined('sugarEntry') || !sugarEntry) {
    die('Not A Valid Entry Point');
}

function post_install()
{
    global $db;
    require_once('modules/ModuleBuilder/MB/ModuleBuilder.php');
    require_once('modules/ModuleBuilder/parsers/parser.dropdown.php');
    require_once('modules/ModuleBuilder/parsers/ParserFactory.php');

    $views = ['detailview', 'editview'];
    foreach ($views as $view) {
        $parser = ParserFactory::getParser($view, "Meetings");
        $parser->_viewdefs['panels']['LBL_CALENDSYNC_PANEL'] = [
            [
                ['name' => 'calendsync_event_name'],
                ['name' => 'calendsync_duration'],
            ],
            [
                ['name' => 'calendsync_assigned_to'],
                ['name' => 'calendsync_email'],
            ],
            [
                ['name' => 'calendsync_uuid'],
                ['name' => 'calendsync_cancel_reason'],
            ],
            [
                ['name' => 'calendsync_q1'],
                ['name' => 'calendsync_a1'],
            ],
            [
                ['name' => 'calendsync_q2'],
                ['name' => 'calendsync_a2'],
            ],
            [
                ['name' => 'calendsync_q3'],
                ['name' => 'calendsync_a3'],
            ],
            [
                ['name' => 'calendsync_q4'],
                ['name' => 'calendsync_a4'],
            ],
            [
                ['name' => 'calendsync_q5'],
                ['name' => 'calendsync_a5'],
            ],
            [
                ['name' => 'calendsync_q6'],
                ['name' => 'calendsync_a6'],
            ],
            [
                ['name' => 'calendsync_q7'],
                ['name' => 'calendsync_a7'],
            ],
            [
                ['name' => 'calendsync_q8'],
                ['name' => 'calendsync_a8'],
            ],
            [
                ['name' => 'calendsync_q9'],
                ['name' => 'calendsync_a9'],
            ],
            [
                ['name' => 'calendsync_q10'],
                ['name' => 'calendsync_a10'],
            ],
            [
                ['name' => 'calendsync_no_show']
            ],
            [
                [
                    'name' => 'calendsync_cancel_button',
                    'customCode' => '<input type="button" class="button" value="Cancel Meeting" onclick="window.open(\'{$fields.calendsync_cancel_link.value}\', \'_blank\');" />'
                ],
                [
                    'name' => 'calendsync_reschedule_button',
                    'customCode' => '<input type="button" class="button" value="Reschedule Meeting" onclick="window.open(\'{$fields.calendsync_reschedule_link.value}\', \'_blank\');" />'
                ]
            ],
        ];
        if ($view === 'detailview') {
            $parser->handleSave(false);
        }
    }

    require_once("modules/Administration/QuickRepairAndRebuild.php");
    $repairClear = new RepairAndClear();
    $repairClear->repairAndClearAll(array('clearAll'), array(translate('LBL_ALL_MODULES')), 'false', 'false');

    $sql = "SHOW COLUMNS FROM meetings LIKE 'calendsync_event_name'";
    $result = $db->query($sql);
    if ($result->num_rows < 1) {
        $sql = "ALTER TABLE meetings
                ADD COLUMN calendsync_event_name varchar(255) NULL,
                ADD COLUMN calendsync_duration varchar(255) NULL,
                ADD COLUMN calendsync_assigned_to varchar(255) NULL,
                ADD COLUMN calendsync_email varchar(255) NULL,
                ADD COLUMN calendsync_uuid varchar(255) NULL,
                ADD COLUMN calendsync_cancel_link varchar(255) NULL,
                ADD COLUMN calendsync_reschedule_link varchar(255) NULL,
                ADD COLUMN calendsync_cancel_reason text NULL
                ADD COLUMN calendsync_q1 text DEFAULT NULL,
                ADD COLUMN calendsync_a1 text DEFAULT NULL,
                ADD COLUMN calendsync_q2 text DEFAULT NULL,
                ADD COLUMN calendsync_a2 text DEFAULT NULL,
                ADD COLUMN calendsync_q3 text DEFAULT NULL,
                ADD COLUMN calendsync_a3 text DEFAULT NULL,
                ADD COLUMN calendsync_q4 text DEFAULT NULL,
                ADD COLUMN calendsync_a4 text DEFAULT NULL,
                ADD COLUMN calendsync_q5 text DEFAULT NULL,
                ADD COLUMN calendsync_a5 text DEFAULT NULL,
                ADD COLUMN calendsync_q6 text DEFAULT NULL,
                ADD COLUMN calendsync_a6 text DEFAULT NULL,
                ADD COLUMN calendsync_q7 text DEFAULT NULL,
                ADD COLUMN calendsync_a7 text DEFAULT NULL,
                ADD COLUMN calendsync_q8 text DEFAULT NULL,
                ADD COLUMN calendsync_a8 text DEFAULT NULL,
                ADD COLUMN calendsync_q9 text DEFAULT NULL,
                ADD COLUMN calendsync_a9 text DEFAULT NULL,
                ADD COLUMN calendsync_q10 text DEFAULT NULL,
                ADD COLUMN calendsync_a10 text DEFAULT NULL,
                ADD COLUMN calendsync_no_show varchar(255) NULL DEFAULT NULL";
        $db->query($sql);
    }

    $sql = "CREATE TABLE IF NOT EXISTS calendsync_tokens (
            id INT AUTO_INCREMENT PRIMARY KEY,
            client_id VARCHAR(255) NOT NULL DEFAULT '',
            client_secret VARCHAR(255) NOT NULL DEFAULT '',
            redirect_uri VARCHAR(255) NOT NULL DEFAULT '',
            authorize_code VARCHAR(255) NOT NULL DEFAULT '',
            owner VARCHAR(255) NOT NULL DEFAULT '',
            access_token text NOT NULL DEFAULT '',
            refresh_token text NOT NULL DEFAULT '',
            organization text NOT NULL DEFAULT '',
            expires_at DATETIME NOT NULL DEFAULT NOW())";
    $db->query($sql);

    $sql = "TRUNCATE TABLE calendsync_tokens";
    $db->query($sql);
}
