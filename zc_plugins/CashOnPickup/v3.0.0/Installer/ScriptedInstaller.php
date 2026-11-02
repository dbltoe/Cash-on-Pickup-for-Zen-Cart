<?php
/**
 * Cash on Pickup -- Plugin Manager installer.
 *
 * The payment module's settings are created the usual way, when the store
 * owner installs it under Modules > Payment, so this only checks the ground
 * the plugin stands on and clears away what the pre-plugin versions (through
 * v2.0.0) left behind. Those settings use the same keys, so a store's choices
 * survive the move.
 *
 * Limited to the ScriptedInstaller API on every supported release (v1.5.8 ->
 * v3.0.0): executeInstallerSql() only, and executeUpgrade() with an optional
 * argument. The module class is never loaded here. Plain SQL needs no class
 * and can't redeclare one during an upgrade.
 *
 * @package  CashOnPickup
 * @license  https://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU Public License V2.0
 */

use Zencart\PluginSupport\ScriptedInstaller as ScriptedInstallBase;

class ScriptedInstaller extends ScriptedInstallBase
{
    public const MIN_ZC_VERSION = '1.5.8';
    public const MIN_PHP_VERSION = '7.4.0';
    /** plugin_control.unique_key: the zc_plugins folder name. */
    public const PLUGIN_KEY = 'CashOnPickup';
    /**
     * Below this release the payment class (through 2.0.x) or the order page
     * (2.1.x) looks for a module only in the core folders, so two bridge files
     * there hand off to the plugin. The owner uploads them; this installer
     * never writes into the core folders.
     */
    public const BRIDGE_BELOW_ZC_VERSION = '2.2.0';
    public const BRIDGE_FOLDER = 'for_zen_cart_1.5.8_to_2.1.x';
    public const BRIDGE_FILES = [
        'includes/modules/payment/cop.php',
        'includes/languages/english/modules/payment/lang.cop.php',
    ];
    /** Only the bridge files carry this, so it tells them from the old module. */
    public const BRIDGE_MARKER = 'cop_bridge_file';
    /** The pre-plugin package's language file before it took the lang. prefix. */
    public const LEGACY_CATALOG_FILES = [
        'includes/languages/english/modules/payment/cop.php',
    ];
    public const LEGACY_ADMIN_FILES = [
        'includes/auto_loaders/config.cash_on_pickup.php',
        'includes/init_includes/init_cash_on_pickup.php',
        'includes/installers/cash_on_pickup/1_2_0.php',
        'includes/languages/english/extra_definitions/lang.cash_on_pickup.php',
        'includes/languages/english/extra_definitions/cash_on_pickup.php',
    ];

    protected function executeInstall()
    {
        if (!$this->copRequirementsMet()) {
            return false;
        }
        $this->copClearLegacyRows();
        $this->copAddGuestSetting();
        $this->copRefreshPluginDescription(true);
        return true;
    }

    /**
     * v2.1 and later pass $oldVersion; declared optional because 2.0.x's
     * executeUpgrade() takes no argument.
     */
    protected function executeUpgrade($oldVersion = null)
    {
        $this->copClearLegacyRows();
        $this->copAddGuestSetting();
        $this->copRefreshPluginDescription(true);
        return true;
    }

    /**
     * Takes the module out as the Remove button under Modules > Payment would,
     * and off the installed list, so nothing is left pointing at a file that's
     * about to go. Orders keep their payment method text either way.
     */
    protected function executeUninstall()
    {
        $this->executeInstallerSql(
            "DELETE FROM " . TABLE_CONFIGURATION . "
              WHERE configuration_key IN ('MODULE_PAYMENT_COP_STATUS', 'MODULE_PAYMENT_COP_ZONE', 'MODULE_PAYMENT_COP_GUEST',
                                          'MODULE_PAYMENT_COP_ORDER_STATUS_ID', 'MODULE_PAYMENT_COP_SORT_ORDER')"
        );

        $installed = $this->dbConn->Execute(
            "SELECT configuration_value FROM " . TABLE_CONFIGURATION . "
              WHERE configuration_key = 'MODULE_PAYMENT_INSTALLED' LIMIT 1"
        );
        if (!$installed->EOF) {
            $list = array_filter(explode(';', (string)$installed->fields['configuration_value']), 'strlen');
            $without = array_values(array_diff($list, ['cop.php']));
            if (count($without) !== count($list)) {
                $this->executeInstallerSql(
                    "UPDATE " . TABLE_CONFIGURATION . "
                        SET configuration_value = '" . $this->dbConn->prepare_input(implode(';', $without)) . "'
                      WHERE configuration_key = 'MODULE_PAYMENT_INSTALLED' LIMIT 1"
                );
            }
        }

        $this->copRefreshPluginDescription(false);
        return true;
    }

    /**
     * Refuse cleanly rather than leave two copies running. Returning false
     * doesn't stop an install on any release; the errorContainer entry does.
     */
    protected function copRequirementsMet(): bool
    {
        // The admin always has these from application_top; a command-line
        // installer may not, and "no constant" must not read as version 0.
        if ((!defined('PROJECT_VERSION_MAJOR') || !defined('PROJECT_VERSION_MINOR')) && defined('DIR_FS_CATALOG') && is_file(DIR_FS_CATALOG . 'includes/version.php')) {
            require DIR_FS_CATALOG . 'includes/version.php';
        }
        $zcVersion = defined('PROJECT_VERSION_MAJOR') && defined('PROJECT_VERSION_MINOR')
            ? PROJECT_VERSION_MAJOR . '.' . PROJECT_VERSION_MINOR
            : '0.0.0';
        // 1.5.8a must not read as older than 1.5.8: version_compare() takes a
        // trailing letter for a pre-release, so compare the digits only, as core does.
        $zcNumeric = preg_replace('/[^0-9.]/', '', $zcVersion);
        if (version_compare($zcNumeric, self::MIN_ZC_VERSION, '<')) {
            $this->copRefuse('Cash on Pickup needs Zen Cart ' . self::MIN_ZC_VERSION . ' or later; this store runs ' . $zcVersion . '.');
            return false;
        }
        if (version_compare(PHP_VERSION, self::MIN_PHP_VERSION, '<')) {
            $this->copRefuse('Cash on Pickup needs PHP ' . self::MIN_PHP_VERSION . ' or later; this server runs ' . PHP_VERSION . '.');
            return false;
        }

        $needsBridge = version_compare($zcNumeric, self::BRIDGE_BELOW_ZC_VERSION, '<');
        $bridgeNote = $needsBridge
            ? ' Zen Cart ' . $zcVersion . ' also needs the includes folder from the package\'s ' . self::BRIDGE_FOLDER . ' folder uploaded to the store.'
            : '';

        $legacy = $this->copLegacyFiles();
        if ($legacy !== []) {
            $this->copRefuse('An older Cash on Pickup is still on this store, and it can\'t run beside the plugin. Delete these files, then install again: '
                . implode(', ', $legacy) . '. Your Modules > Payment settings aren\'t in those files, so they\'ll carry over.' . $bridgeNote);
            return false;
        }

        if ($needsBridge) {
            $missing = [];
            foreach (self::BRIDGE_FILES as $file) {
                if (!defined('DIR_FS_CATALOG') || !is_file(DIR_FS_CATALOG . $file)) {
                    $missing[] = $file;
                }
            }
            if ($missing !== []) {
                $this->copRefuse('Zen Cart ' . $zcVersion . ' looks for a payment module in its own includes folder, so on this release two more files are needed.'
                    . ' Upload the includes folder from the package\'s ' . self::BRIDGE_FOLDER . ' folder to the store, then install again. Missing: ' . implode(', ', $missing) . '.');
                return false;
            }
        }

        return true;
    }

    /**
     * What the pre-plugin package left on disk, as the paths to delete. A file
     * at a bridge path counts only when it isn't the bridge.
     *
     * @return string[]
     */
    protected function copLegacyFiles(): array
    {
        $found = [];
        if (defined('DIR_FS_ADMIN')) {
            foreach (self::LEGACY_ADMIN_FILES as $file) {
                if (is_file(DIR_FS_ADMIN . $file)) {
                    $found[] = 'YOUR_ADMIN/' . $file;
                }
            }
        }
        if (defined('DIR_FS_CATALOG')) {
            foreach (self::LEGACY_CATALOG_FILES as $file) {
                if (is_file(DIR_FS_CATALOG . $file)) {
                    $found[] = $file;
                }
            }
            foreach (self::BRIDGE_FILES as $file) {
                $path = DIR_FS_CATALOG . $file;
                if (is_file($path) && strpos((string)file_get_contents($path), self::BRIDGE_MARKER) === false) {
                    $found[] = $file;
                }
            }
        }
        return $found;
    }

    /**
     * What the old admin init left in the database on every store that ever
     * ran it, and the wrong title on its status row. The module's settings
     * stay. Safe to repeat, and a no-op on a store that never had it.
     */
    protected function copClearLegacyRows(): void
    {
        $this->executeInstallerSql(
            "DELETE FROM " . TABLE_ADMIN_PAGES . " WHERE page_key = 'configCashOnPickup'"
        );

        $version = $this->dbConn->Execute(
            "SELECT configuration_group_id FROM " . TABLE_CONFIGURATION . "
              WHERE configuration_key = 'CASH_ON_PICKUP_VERSION' LIMIT 1"
        );
        if (!$version->EOF) {
            $groupId = (int)$version->fields['configuration_group_id'];
            $this->executeInstallerSql(
                "DELETE FROM " . TABLE_CONFIGURATION . " WHERE configuration_key = 'CASH_ON_PICKUP_VERSION'"
            );
            $others = $this->dbConn->Execute(
                "SELECT COUNT(*) AS total FROM " . TABLE_CONFIGURATION . " WHERE configuration_group_id = " . $groupId
            );
            if ($groupId > 0 && (int)$others->fields['total'] === 0) {
                $this->executeInstallerSql(
                    "DELETE FROM " . TABLE_CONFIGURATION_GROUP . "
                      WHERE configuration_group_id = " . $groupId . "
                        AND configuration_group_title = 'Cash on Pickup'"
                );
            }
        }

        $this->executeInstallerSql(
            "UPDATE " . TABLE_CONFIGURATION . "
                SET configuration_title = 'Enable Cash on Pickup Module',
                    configuration_description = 'Do you want to accept cash when customers pick up their orders? It shows at checkout only when Store Pickup is the chosen shipping method.'
              WHERE configuration_key = 'MODULE_PAYMENT_COP_STATUS'"
        );
    }

    /**
     * A store moving up from v2.0.0 already has the module installed, so its
     * install() won't run again to add this. Off, the same as a new install.
     * Without the module installed there's nothing to add: install() does it.
     */
    protected function copAddGuestSetting(): void
    {
        $rows = $this->dbConn->Execute(
            "SELECT configuration_key FROM " . TABLE_CONFIGURATION . "
              WHERE configuration_key IN ('MODULE_PAYMENT_COP_STATUS', 'MODULE_PAYMENT_COP_GUEST')"
        );
        $keys = [];
        while (!$rows->EOF) {
            $keys[] = $rows->fields['configuration_key'];
            $rows->MoveNext();
        }
        if (in_array('MODULE_PAYMENT_COP_STATUS', $keys, true) && !in_array('MODULE_PAYMENT_COP_GUEST', $keys, true)) {
            $this->executeInstallerSql(
                "INSERT INTO " . TABLE_CONFIGURATION . "
                    (configuration_title, configuration_key, configuration_value, configuration_description,
                     configuration_group_id, sort_order, set_function, date_added)
                 VALUES ('Allow Guest Checkout', 'MODULE_PAYMENT_COP_GUEST', 'False',
                         'Do you want One Page Checkout guests to be able to use Cash on Pickup? They have no account with you, so it is off unless you turn it on.', 6, 3,
                         'zen_cfg_select_option(array(\'True\', \'False\'), ', now())"
            );
        }
    }

    /**
     * Save the manifest's description again, with the Settings button shown
     * ($installed true: Install, Upgrade) or left out ($installed false:
     * Uninstall). Needed on v1.5.8, v2.0 and v2.1, which store the description
     * only when Plugin Manager first sees the plugin; a no-op in effect on
     * v2.2 and later, which rewrite it from the manifest on every visit.
     *
     * The installer passes the state because this runs inside core's step, and
     * core writes plugin_control.status on the wrong side of it: 1 only after
     * executeInstall() returns, and on 3.0.0 0 only after executeUninstall().
     * require runs manifest.php in this method's scope, so it sees
     * $copInstallerSaysInstalled.
     */
    protected function copRefreshPluginDescription(bool $installed): bool
    {
        if (!defined('TABLE_PLUGIN_CONTROL')) {
            return true;
        }
        $copInstallerSaysInstalled = $installed;
        $manifest = require dirname(__DIR__) . '/manifest.php';
        if (!is_array($manifest) || !isset($manifest['pluginDescription'])) {
            return true;
        }

        return $this->executeInstallerSql(
            "UPDATE " . TABLE_PLUGIN_CONTROL . "
                SET description = '" . $this->dbConn->prepare_input($manifest['pluginDescription']) . "'
              WHERE unique_key = '" . $this->dbConn->prepare_input(self::PLUGIN_KEY) . "'
              LIMIT 1"
        );
    }

    protected function copRefuse(string $message): void
    {
        $this->errorContainer->addError(0, $message, true, $message);
    }
}
