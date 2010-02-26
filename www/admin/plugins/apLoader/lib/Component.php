<?php

require_once MAX_PATH.'/lib/OX/Plugin/Component.php';

class AP_Loader_Component extends OX_Component
{
    const VAR_PREFIX = 'ap_expiry_';
    const HTTP_TIMEOUT = 3;

    public function onEnable()
    {
        $url = MAX::constructURL(MAX_URL_ADMIN, "plugins/{$this->component}/about.php?expiry=1");

        $ctx = stream_context_create(array('http' => array(
            'method' => 'GET',
            'header' => "Cookie: sessionID=".$_COOKIE['sessionID']."\r\n"
        )));
        $fp = @fopen($url, 'rb', false, $ctx);
        if ($fp) {
            stream_set_timeout($fp, self::HTTP_TIMEOUT);
            $expiry = stream_get_contents($fp);
            fclose($fp);
        }

        $this->updateExpiry($expiry);

        return true;
    }

    public function onDisable()
    {
        $this->updateExpiry(false);
        
        return true;
    }

    public function apLoaderMenuEntry()
    {
        return true;
    }

    protected function updateExpiry($expiry)
    {
        if (!empty($expiry)) {
            OA_Dal_ApplicationVariables::set(self::VAR_PREFIX.$this->component, $expiry);
        } else {
            OA_Dal_ApplicationVariables::delete(self::VAR_PREFIX.$this->component);
        }

        $oLoader = OX_Component::factory('admin', 'apLoader');
        $oLoader->notifyPluginExpiry($this, $expiry);
    }

}
