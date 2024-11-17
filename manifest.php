<?php
$manifest = array(
    'acceptable_sugar_versions' =>  array(
        'regex_matches' => array(
            '.*',
        ),
    ),
    'acceptable_sugar_flavors' => array(
        'CE',
        'PRO',
        'ENT',
        'CORP',
        'ULT',
    ),
    'name' => 'Suite CRM Calendly',
    'description' => 'Integrates SuiteCRM with Calendly',
    'version' => '1.0.0',
    'author' => 'Unnikrishnan Thankappan',
    'is_uninstallable' => true,
    'type' => 'module',
    'published_date' => '2024-11-15',
    'readme' => 'README.txt',
);

$installdefs = array(
    'id' => 'calendsync',
    'copy' =>
    array(
        0 =>
        array(
            'from' => '<basepath>/modules/calendsync',
            'to' => 'modules/calendsync',
        ),
        1 =>
        array(
            'from' => '<basepath>/custom/Extension/application/Ext/EntryPointRegistry/calendsync.php',
            'to' => 'custom/Extension/application/Ext/EntryPointRegistry/calendsync.php',
        ),
        2 =>
        array(
            'from' => '<basepath>/custom/Extension/modules/Meetings/Ext/Vardefs',
            'to' => 'custom/Extension/modules/Meetings/Ext/Vardefs',
        ),
        3 =>
        array(
            'from' => '<basepath>/custom/Extension/modules/Meetings/Ext/Language/en_us.suite_calendsync.php',
            'to' => 'custom/Extension/modules/Meetings/Ext/Language/en_us.suite_calendsync.php',
        )
    ),
    'post_install' => array(
        0 => '<basepath>/scripts/post_install.php'
    )
);
