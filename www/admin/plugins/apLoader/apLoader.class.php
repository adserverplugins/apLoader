<?php

require_once LIB_PATH.'/Plugin/Component.php';

class Plugins_admin_apLoader_apLoader extends OX_Component
{
    static $REGISTER_NAME = 'apLoaderRegister';

    public function afterLogin()
    {
        if (OA_Permission::isUserLinkedToAdmin()) {
            $this->scheduleRegisterNotification();
        }
    }

    public function onEnable()
    {
        $this->scheduleRegisterNotification();
        return true;
    }

    public function onDisable()
    {
        $this->removeRegisterNotification();
        return true;
    }

    public function scheduleRegisterNotification()
    {
        $this->removeRegisterNotification();

        if (!function_exists('sg_load')) {
            $url = MAX::constructURL(MAX_URL_ADMIN, 'plugins/apLoader/');

            $message = "The Adserverplugins.com loader requires you to perform
                some <a href=\"{$url}\">configuration steps &raquo;</a>";

            OA_Admin_UI::getInstance()
                ->getNotificationManager()
                ->queueNotification($message, 'warning', self::$REGISTER_NAME);
        }

    }

    public function removeRegisterNotification()
    {
        OA_Admin_UI::getInstance()
            ->getNotificationManager()
            ->removeNotifications(self::$REGISTER_NAME);
    }

    public function updateMenu()
    {
        if (!function_exists('sg_load')) {
            return;
        }
        $oMenu = OA_Admin_Menu::singleton();
        $aPlugins = OX_Component::getListOfRegisteredComponentsForHook('apLoaderMenuEntry');
        foreach ($aPlugins as $id) {
            if ($obj = OX_Component::factoryByComponentIdentifier($id)) {
                if ($obj->apLoaderMenuEntry()) {
                    $oMenu->addTo(
                        'adserverplugins',
                        new OA_Admin_Menu_Section(
                            'apAbout-'.$obj->component, $obj->component,
                            'plugins/' . $obj->group . '/about.php',
                            false, null, array(), 1, true)
                    );
                }
            }
        }
    }
}
