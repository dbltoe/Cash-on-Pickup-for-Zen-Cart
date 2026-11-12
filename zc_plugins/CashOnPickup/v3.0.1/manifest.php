<?php
/**
 * Cash on Pickup -- plugin manifest.
 *
 * The description is echoed as raw HTML in the Plugin Manager's info panel,
 * which is also where the Install / Uninstall buttons live, so the ReadMe,
 * Settings, Forum Thread and GitHub buttons are placed here.
 *
 * ReadMe opens README.md through the plugin's own admin page
 * (index.php?cmd=cash_on_pickup_readme): behind the admin login, superusers
 * only, in its own window. It is shown only to superusers; the page enforces
 * that too, because Zen Cart 1.5.8-2.1 keep the description they first saw.
 * Forum Thread and GitHub go off-site, so they open in a new window with
 * rel="noopener noreferrer" (zencart/zencart#8031).
 *
 * On v2.2 and later the description is refreshed from this file on every
 * Plugin Manager scan; on v1.5.8, v2.0 and v2.1 it is captured once, when the
 * plugin_control row is first created, and only this plugin's installer
 * refreshes it there (at Install, Upgrade and Uninstall).
 *
 * @package  CashOnPickup
 * @license  https://www.gnu.org/licenses/gpl-3.0.html GNU General Public License v3.0
 */

$copReadmeUrl = 'index.php?cmd=cash_on_pickup_readme';
// Zen Cart 3.0.0 reads every manifest on every admin page, early (template resolver),
// before admin access is set up: zen_is_superuser() there is a fatal "Undefined constant
// SUPERUSER_PROFILE" that takes down the whole admin. Ask only once that exists.
$copShowReadme = true;
if (function_exists('zen_is_superuser') && defined('SUPERUSER_PROFILE')) {
    $copShowReadme = zen_is_superuser();
}
$copGithubUrl = 'https://github.com/dbltoe/Cash-on-Pickup-for-Zen-Cart';
$copForumUrl = 'https://www.zen-cart.com/threads/207082?page=1#post-1344942';

/**
 * The Settings button: Modules > Payment with this module selected, shown only
 * once the plugin is installed in Plugin Manager. "Installed" has to be right
 * at the moment the description is saved. The installer's save runs INSIDE
 * core's step, which writes plugin_control.status on the wrong side of it, so
 * the installer sets $copInstallerSaysInstalled in its own scope just before
 * it requires this file. Everywhere else core has finished, and status is the
 * truth: installed is any status but 0 (1 enabled, 2 disabled). That read is
 * guarded so a manifest read with no database just leaves the button out.
 */
if (isset($copInstallerSaysInstalled)) {
    $copIsInstalled = ($copInstallerSaysInstalled === true);
} else {
    $copIsInstalled = false;
    if (defined('IS_ADMIN_FLAG') && IS_ADMIN_FLAG === true && defined('TABLE_PLUGIN_CONTROL')
        && isset($GLOBALS['db']) && is_object($GLOBALS['db'])) {
        $copControl = $GLOBALS['db']->Execute(
            "SELECT status FROM " . TABLE_PLUGIN_CONTROL
            . " WHERE unique_key = 'CashOnPickup' LIMIT 1"
        );
        $copIsInstalled = !$copControl->EOF && (int)$copControl->fields['status'] !== 0;
    }
}
$copSettingsUrl = $copIsInstalled ? 'index.php?cmd=modules&set=payment&module=cop' : '';

/**
 * One row: ReadMe | Settings | Forum Thread | GitHub. A second line would push
 * the Author line and the core buttons further down the panel. Every gap is
 * 4px and the side padding 8px, so all four fit from a 1366px-wide window up.
 * Settings opens in the same tab (it's the admin itself); the others open a
 * new one. ReadMe is left out for an admin who isn't a superuser.
 */
$copGap = '4px';
$copButtonStyle = ' style="margin:0 ' . $copGap . ' 0 0;padding-left:8px;padding-right:8px"';
$copButton = static function ($url, $label, $newTab) use ($copButtonStyle) {
    return '<a href="' . htmlspecialchars($url, ENT_QUOTES, 'UTF-8') . '"'
        . ($newTab ? ' target="_blank" rel="noopener noreferrer"' : '')
        . ' class="btn btn-primary" role="button"' . $copButtonStyle . '>' . $label . '</a>';
};

$copLinks =
    '<div style="margin:10px 0 0;padding:0 0 0 ' . $copGap . '">'
    . ($copShowReadme ? $copButton($copReadmeUrl, 'ReadMe', true) : '')
    . ($copSettingsUrl !== '' ? $copButton($copSettingsUrl, 'Settings', false) : '')
    . $copButton($copForumUrl, 'Forum Thread', true)
    . $copButton($copGithubUrl, 'GitHub', true)
    . '</div>';

return [
    'pluginVersion' => 'v3.0.1',
    'pluginName' => 'Cash on Pickup',
    'pluginDescription' =>
        'Lets customers pay cash when they pick up their order. It shows at checkout only when they choose '
        . 'Store Pickup shipping, on the standard checkout and on One Page Checkout. '
        . 'Install it here, then enable and configure it under Modules &gt; Payment.'
        . $copLinks,
    'pluginAuthor' => 'My Zen Cart Host (dbltoe)',
    'pluginId' => 1242,
    'zcVersions' => ['v158', 'v200', 'v210', 'v220', 'v230', 'v300'],
    'changelog' => 'changelog.txt',
    'github_repo' => $copGithubUrl,
    'pluginGroups' => [],
];
