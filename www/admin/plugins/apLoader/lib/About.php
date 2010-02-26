<?php

class AP_Loader_About
{
    protected $plugin;
    protected $encoded;
    protected $license;
    protected $expiryTimestamp;
    protected $expiryDate;
    protected $expiryDays;

    public function __construct($plugin)
    {
        $this->plugin = $plugin;
        $this->encoded = function_exists('sg_get_const') && sg_get_const('encoder');

        if ($this->encoded) {
            $license = sg_get_const('licensed_to');
            $expiry = sg_get_const('expire_date');

            $this->license = $license ? $license : 'n/a';
            $this->expiryTimestamp = $expiry;
            $this->expiryDate = $expiry ? date('Y-m-d H:i:s', $expiry) : 'n/a';
            $this->expiryDays = $expiry ? floor(($expiry - time()) / 86400) : 'n/a';
        }
    }

    public function handleRequest($aGet)
    {
        OA_Permission::enforceAccount(OA_ACCOUNT_ADMIN);
        MAX_commonSetNoCacheHeaders();

        if (empty($aGet['expiry'])) {
            $this->display();
        } else {
            echo $this->expiryTimestamp;
        }
    }

    public function display()
    {
        $oLoader = OX_Component::factory('admin', 'apLoader');
        $oLoader->updateMenu();

        phpAds_PageHeader('apAbout-'.$this->plugin, '', '../../');

        if ($this->encoded) {
            ?>
            <h4>This plugin is encoded</h4>
            <ul>
                <li>Licensed to: <?php echo $this->license; ?></li>
                <li>Expire date: <?php echo $this->expiryDate; ?></li>
                <li>Days remaining: <?php echo $this->expiryDays; ?></li>
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