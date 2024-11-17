<?php
if (!defined('sugarEntry') || !sugarEntry) {
    die('Not A Valid Entry Point');
}

function post_install()
{
    global $db;
    require_once ('modules/ModuleBuilder/MB/ModuleBuilder.php');
    require_once('modules/ModuleBuilder/parsers/parser.dropdown.php');
    require_once('modules/ModuleBuilder/parsers/ParserFactory.php');
    $sql = "ALTER TABLE meetings
            ADD COLUMN calendsync_event_name varchar(255) NULL,
            ADD COLUMN calendsync_duration varchar(255) NULL,
            ADD COLUMN calendsync_assigned_to varchar(255) NULL,
            ADD COLUMN calendsync_email varchar(255) NULL,
            ADD COLUMN calendsync_uuid varchar(255) NULL,
            ADD COLUMN calendsync_cancel_reason text NULL";
    $db->query($sql);

    $sql = "CREATE TABLE IF NOT EXISTS calendsync_tokens (
            id INT AUTO_INCREMENT PRIMARY KEY,
            client_id VARCHAR(255) NOT NULL DEFAULT '',
            client_secret VARCHAR(255) NOT NULL DEFAULT '',
            owner VARCHAR(255) NOT NULL DEFAULT '',
            access_token text NOT NULL DEFAULT '',
            refresh_token text NOT NULL DEFAULT '',
            expires_at DATETIME NOT NULL DEFAULT NOW())";
    $db->query($sql);

    $sql = "TRUNCATE TABLE calendsync_tokens";
    $db->query($sql);

    $views = ['detailview', 'editview'];
    foreach ($views as $view) {
        $parser = ParserFactory::getParser($view, "Meetings");
        if (!isset($parser->_viewdefs['panels']['LBL_CALENDSYNC_PANEL'])) {
            $parser->_viewdefs['panels']['LBL_CALENDSYNC_PANEL'] = [
                [
                    ['name' => 'calendsync_event_name'],
                    ['name' => 'calendsync_duration'],
                    ['name' => 'calendsync_assigned_to'],
                    ['name' => 'calendsync_email'],
                    ['name' => 'calendsync_uuid'],
                    ['name' => 'calendsync_cancel_reason']
                ]
            ];
            if ($view === 'detailview') {
                $parser->handleSave(false);
            }
        }
    }

    require_once("modules/Administration/QuickRepairAndRebuild.php");
    $repairClear = new RepairAndClear();
    $repairClear->repairAndClearAll(array('clearAll'), array(translate('LBL_ALL_MODULES')), 'false', 'false');
}
