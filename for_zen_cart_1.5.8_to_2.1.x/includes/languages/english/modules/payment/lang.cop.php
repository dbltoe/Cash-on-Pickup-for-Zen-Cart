<?php
/**
 * Cash on Pickup -- language bridge for Zen Cart 1.5.8 through 2.1.x.
 *
 * Those releases load this module's language file only from this folder, so
 * this returns the plugin's own definitions. Upload it together with
 * includes/modules/payment/cop.php (the note there says when). Another
 * language needs a copy of this file under its own folder; the text is the
 * plugin's English either way.
 *
 * @package  CashOnPickup
 * @license  https://www.gnu.org/licenses/gpl-3.0.html GNU General Public License v3.0
 */

if (!defined('DIR_FS_CATALOG')) {
    return [];
}

if (!function_exists('cop_bridge_file')) {
    /**
     * The path of a file inside the plugin, or '' when the plugin folder is
     * gone. The version the Plugin Manager has installed is preferred; with
     * none recorded (the language file is asked for before the install), the
     * highest version folder present is used.
     */
    function cop_bridge_file($relative)
    {
        global $installedPlugins;
        $base = DIR_FS_CATALOG . 'zc_plugins/CashOnPickup/';
        $versions = [];
        if (is_array($installedPlugins)) {
            foreach ($installedPlugins as $plugin) {
                if (is_array($plugin) && isset($plugin['unique_key'], $plugin['version']) && $plugin['unique_key'] === 'CashOnPickup') {
                    $versions[] = $plugin['version'];
                }
            }
        }
        $folders = glob($base . 'v*', GLOB_ONLYDIR);
        if (is_array($folders) && $folders !== []) {
            $folders = array_map('basename', $folders);
            usort($folders, 'version_compare');
            $versions = array_merge($versions, array_reverse($folders));
        }
        foreach ($versions as $version) {
            $file = $base . $version . '/' . $relative;
            if (is_file($file)) {
                return $file;
            }
        }
        return '';
    }
}

$copBridgeLang = cop_bridge_file('catalog/includes/languages/english/modules/payment/lang.cop.php');
$define = ($copBridgeLang !== '') ? require $copBridgeLang : [];
// With the plugin folder gone, 2.1 reads an empty list as a missing language
// file and never reaches the placeholder class, whose title says what to do.
if (!is_array($define) || $define === []) {
    $define = ['MODULE_PAYMENT_COP_TEXT_TITLE' => 'Cash on Pickup'];
}
unset($copBridgeLang);

return $define;
