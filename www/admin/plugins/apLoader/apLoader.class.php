<?php

require_once LIB_PATH.'/Plugin/Component.php';
require_once LIB_PATH . '/Admin/Redirect.php';

class Plugins_admin_apLoader_apLoader extends OX_Component
{
    static $REGISTER_NAME = 'apLoaderRegister';

    public function afterLogin()
    {
        if (OA_Permission::isUserLinkedToAdmin()) {
            self::scheduleRegisterNotification();
        }
    }

    public function apLoaderHook()
    {
        return true;
    }

    public function onEnable()
    {
        self::scheduleRegisterNotification();
        return true;
    }

    public function onDisable()
    {
        self::removeRegisterNotification();
        return true;
    }

    static public function scheduleRegisterNotification()
    {
        self::removeRegisterNotification();

        if (!function_exists('sg_load')) {
            $url = MAX::constructURL(MAX_URL_ADMIN, 'plugins/apLoader/');

            $message = "The Adserverplugins.com loader requires you to perform
                some <a href=\"{$url}\">configuration steps &raquo;</a>";

            OA_Admin_UI::getInstance()
                ->getNotificationManager()
                ->queueNotification($message, 'warning', self::$REGISTER_NAME);
        }

    }

    static public function removeRegisterNotification()
    {
        OA_Admin_UI::getInstance()
            ->getNotificationManager()
            ->removeNotifications(self::$REGISTER_NAME);
    }

    static public function updateMenu()
    {
        if (!function_exists('sg_load')) {
            return;
        }
        $oMenu = OA_Admin_Menu::singleton();
        $aPlugins = OX_Component::getListOfRegisteredComponentsForHook('apLoaderHook');
        foreach ($aPlugins as $id) {
            if ($obj = OX_Component::factoryByComponentIdentifier($id)) {
                if ($obj->apLoaderHook()) {
                    $oMenu->addTo(
                        'adserverplugins',
                        new OA_Admin_Menu_Section(
                            strtolower($obj->component).'-about', $obj->component,
                            'plugins/' . $obj->group . '/about.php',
                            false, null, array(), 1, true)
                    );
                }
            }
        }
    }

    static public function displayAbout($plugin)
    {
        OA_Permission::enforceAccount(OA_ACCOUNT_ADMIN);
        MAX_commonSetNoCacheHeaders();
        
        self::updateMenu();
        phpAds_PageHeader($plugin.'-about', '', '../../');

        $encoded = function_exists('sg_get_const') && sg_get_const('encoder');

        if ($encoded) {
            $license = sg_get_const('licensed_to');
            $license = $license ? $license : 'n/a';

            $expiry = sg_get_const('expire_date');
            $expiry  = $expiry ? date('Y-m-d H:i:s', $expiry) : 'n/a'

            ?>
            <h4>This plugin is encoded</h4>
            <ul>
                <li>Licensed to: <?php echo $license; ?></li>
                <li>Expire date: <?php echo $expiry; ?></li>
            </ul>
            <?php
        } else {
            ?>
            <h4>This plugin is not encoded</h4>
            <?php
        }

        phpAds_PageFooter();
    }
}
