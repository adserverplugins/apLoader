<?php

class AP_Loader_About
{
    protected $plugin;
    protected $encoded;
    protected $license;
    protected $expiry;

    public function __construct($plugin)
    {
        $this->plugin = $plugin;
        $this->encoded = function_exists('sg_get_const') && sg_get_const('encoder');

        if ($this->encoded) {
            $license = sg_get_const('licensed_to');
            $expiry = sg_get_const('expire_date');

            $this->license = $license ? $license : 'n/a';
            $this->expiry  = $expiry ? date('Y-m-d H:i:s', $expiry) : 'n/a';
        }
    }

    static public function display()
    {
        OA_Permission::enforceAccount(OA_ACCOUNT_ADMIN);
        MAX_commonSetNoCacheHeaders();

        self::updateMenu();
        phpAds_PageHeader('apAbout-'.$this->plugin, '', '../../');

        if ($this->encoded) {
            ?>
            <h4>This plugin is encoded</h4>
            <ul>
                <li>Licensed to: <?php echo $this->license; ?></li>
                <li>Expire date: <?php echo $this->expiry; ?></li>
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