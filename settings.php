<?php

defined('MOODLE_INTERNAL') || die();

if ($hassiteconfig) {  // This checks if the user can edit site configuration
    $settings = new admin_settingpage('local_openlrs', get_string('pluginname', 'local_openlrs'));

    // Add a field for the External openLRS Path
    $settings->add(new admin_setting_configtext(
        'local_openlrs/externalpath',
        get_string('externalpath', 'local_openlrs'),
        get_string('externalpath_desc', 'local_openlrs'),
        'https://www.example.com/',  // Default value
        PARAM_URL
    ));

    // Add a field for the Secret Key
    $settings->add(new admin_setting_configtext(
        'local_openlrs/secretkey',
        get_string('secretkey', 'local_openlrs'),
        get_string('secretkey_desc', 'local_openlrs'),
        '',  // Default value
        PARAM_ALPHANUMEXT  // Alphanumeric and some extended characters are allowed
    ));

    // Add a field for Consumer ID
    $settings->add(new admin_setting_configtext(
        'local_openlrs/consumerid',
        get_string('consumerid', 'local_openlrs'),
        get_string('consumerid_desc', 'local_openlrs'),
        'KI-Campus',  // Default value
        PARAM_ALPHANUMEXT  // Alphanumeric and some extended characters are allowed
    ));

    // Add the settings page to Moodle's admin tree
    $ADMIN->add('localplugins', $settings);
}

?>