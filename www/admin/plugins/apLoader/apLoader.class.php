<?php

require_once MAX_PATH.'/www/admin/plugins/apLoader/lib/Component.php';

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

    protected function getExpiringPlugins()
    {
        $aPlugins = OX_Component::getListOfRegisteredComponentsForHook('apLoaderMenuEntry');
        $aResult = array();
        foreach ($aPlugins as $id) {
            list(, , $component) = OX_Component::parseComponentIdentifier($id);
            $expiry = OA_Dal_ApplicationVariables::get(AP_Loader_Component::VAR_PREFIX.$component);
            $aResult[$id] = $expiry;
        }
        return $aResult;
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
        } else {
            foreach ($this->getExpiringPlugins() as $id => $expiry) {
                if ($obj = OX_Component::factoryByComponentIdentifier($id)) {
                    $this->notifyPluginExpiry($obj, $expiry);
                }
            }
        }

    }

    public function removeRegisterNotification()
    {
        OA_Admin_UI::getInstance()
            ->getNotificationManager()
            ->removeNotifications(self::$REGISTER_NAME);

        if (function_exists('sg_load')) {
            foreach (OX_Component::getListOfRegisteredComponentsForHook('apLoaderMenuEntry') as $id) {
                if ($obj = OX_Component::factoryByComponentIdentifier($id)) {
                    $this->notifyPluginExpiry($obj, false);
                }
            }
        }
    }

    public function notifyPluginExpiry($obj, $expiry)
    {
        $register = self::$REGISTER_NAME.'_'.$obj->component;
        if ($expiry) {
            $url = MAX::constructURL(MAX_URL_ADMIN, "plugins/{$obj->group}/about.php");
            $days = floor(($expiry - time()) / 86400);
            if ($days >= 0) {
                $message = "The {$obj->component} plugin will expire in {$days} day(s).";
            } else {
                $message = "The {$obj->component} plugin is expired.";
            }
            $message .= "<br /><a href=\"{$url}\">Read details &raquo;</a>";

            OA_Admin_UI::getInstance()
                ->getNotificationManager()
                ->queueNotification($message, 'warning', $register);
        } else {
            OA_Admin_UI::getInstance()
                ->getNotificationManager()
                ->removeNotifications($register);
        }
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
