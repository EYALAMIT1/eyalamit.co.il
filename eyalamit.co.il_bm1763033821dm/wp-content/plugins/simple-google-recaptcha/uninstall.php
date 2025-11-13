<?php

if (!defined('WP_UNINSTALL_PLUGIN')) {
    die('Direct access not allowed');
}


/**
 * Class SimpleGoogleRecaptchaUninstall
 */
class SimpleGoogleRecaptchaUninstall
{
    /**
     * SimpleGoogleRecaptchaUninstall constructor.
     */
    public function __construct()
    {
        $this->sgr_delete(['site_key', 'secret_key', 'login_disable', 'version']);
    }

    private function sgr_delete($array)
    {
        foreach ($array as $item) {
            delete_option(sprintf('sgr_%s', $item));
        }
    }
}

new SimpleGoogleRecaptchaUninstall();
