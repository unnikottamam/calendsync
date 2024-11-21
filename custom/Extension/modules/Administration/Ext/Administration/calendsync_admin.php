<?php
global $sugar_version;

$admin_option_defs = [];
$admin_option_defs['Administration']['calendsync_apiconfig'] = [
    '',
    'LBL_CALENDSYNC_CONFIGURATION',
    'LBL_CALENDSYNC_MESSAGE',
    './index.php?module=calendsync&action=EditView'
];
$admin_group_header[] = ['LBL_CALENDSYNC', '', false, $admin_option_defs, ''];
