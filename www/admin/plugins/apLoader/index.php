<?php

/**
 * apLoader for the OpenX ad server
 *
 * @author Matteo Beccati
 * @copyright 2010 AdserverPlugins.com - All rights reserved
 *
 * $Id$
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
$oLoader->scheduleRegisterNotification();

// OB
ob_start();

// SG How-to
if (function_exists('sg_load')) {
?>
    <h3>The Sourceguardian extension is correctly installed.</h3>

    <p style="margin-top: 1em; line-height: 1.5em">This means that you will be able to run encoded plugins from
        AdserverPlugins.com.</p>
<?php
} else {
?>
    <h3>The Sourceguardian extension is not installed.</h3>

    <p style="margin-top: 1em; line-height: 1.5em">This means that you will not be able to run any encoded plugin from
        AdserverPlugins.com.</p>
    <p style="margin-top: 1em; line-height: 1.5em">Please click on the button below to submit your PHP configuration to the the
        SourceGuardian website and perform the auto-detection.</p>
    <form method="post" action="http://www.sourceguardian.com/loaders/download.php" target="_blank">
        <input type="hidden" name="phpinfo_link" value="http://" />
	<textarea name="phpinfo_data" rows="10" cols="40" style="display: none">
            <?php
            ob_start();
            phpinfo();
            echo htmlspecialchars(ob_get_clean());
            ?>
        </textarea>
        <input type="submit" name="submit" value="Detect">
    </form>
<?php
}

// OB
$content = ob_get_clean();

// Display the OpenX page header
phpAds_PageHeader('aploader-info', '', '../../');

echo $content;

// Display the OpenX page footer
phpAds_PageFooter();
