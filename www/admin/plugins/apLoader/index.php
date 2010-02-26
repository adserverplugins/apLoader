<?php
/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

// Prepare the OpenX environment via standard external OpenX scripts
require_once '../../../../init.php';
require_once '../../config.php';

require_once dirname(__FILE__).'/apLoader.class.php';

// Limit access to the administrator
OA_Permission::enforceAccount(OA_ACCOUNT_ADMIN);

// No cache
MAX_commonSetNoCacheHeaders();

// Update menu
$oLoader = OX_Component::factory('admin', 'apLoader');
$oLoader->updateMenu();

// OB
ob_start();

// SG How-to
if (function_exists('sg_load')) {
    $oLoader->scheduleRegisterNotification();
?>
    <h3>The Sourceguardian extension is correctly installed.</h3>

    <p>This means that you will be able to run encoded plugins from
        AdserverPlugins.com.</p>
<?php
} else {
    Plugins_admin_apLoader_apLoader::scheduleRegisterNotification();
?>
    <h3>The Sourceguardian extension is not installed.</h3>

    <p>This means that you will not be able to run any encoded plugin from
        AdserverPlugins.com.</p>
    <p>Please follow the instruction below, or send them to your system
        administrator.</p>
    <div>
        <?php include './sg/howto-install.php'; ?>
    </div>
<?php
}

// OB
$content = ob_get_clean();

// Display the OpenX page header
phpAds_PageHeader('aploader-info', '', '../../');

echo $content;

// Display the OpenX page footer
phpAds_PageFooter();
