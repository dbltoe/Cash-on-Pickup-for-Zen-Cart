<?php
/**
 * Cash on Pickup payment module.
 *
 * The customer pays cash when collecting the order, so the module is offered
 * only when Store Pickup is the chosen shipping method. It has no gateway and
 * nothing to process; the settings live under Modules > Payment like any core
 * payment module's.
 *
 * Original module by Stephen Hewitson, updated by Design75 (Zen4All), rebuilt
 * as an encapsulated plugin by My Zen Cart Host (dbltoe).
 *
 * @package  CashOnPickup
 * @license  https://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU Public License V2.0
 */

if (!defined('IS_ADMIN_FLAG')) {
    die('Illegal Access');
}

class cop
{
    /**
     * What this payment page was built for, kept for the One Page Checkout
     * observer: OPC changes the shipping method without rebuilding the payment
     * block, so it needs to know when that change crosses in or out of pickup.
     */
    public const SESSION_KEY = 'cash_on_pickup';

    /**
     * The shipping module this payment method belongs to. Matched as a part
     * of the shipping id: a store with several pickup locations has ids such
     * as storepickup_storepickup0.
     */
    public const PICKUP_SHIPPING_CODE = 'storepickup';

    /**
     * Declared so check() doesn't create a dynamic property, which PHP 8.2 and
     * later log as deprecated on every visit to Modules > Payment.
     *
     * @var int
     */
    protected $_check;
    /**
     * @var string
     */
    public $code;
    /**
     * @var string
     */
    public $description;
    /**
     * @var bool
     */
    public $enabled;
    /**
     * @var int
     */
    public $order_status;
    /**
     * @var string
     */
    public $title;
    /**
     * Null until the module is installed under Modules > Payment.
     *
     * @var int|null
     */
    public $sort_order;

    public function __construct()
    {
        global $order;

        $this->code = 'cop';
        $this->title = $this->text('MODULE_PAYMENT_COP_TEXT_TITLE', 'Cash on Pickup');
        $this->description = $this->text('MODULE_PAYMENT_COP_TEXT_DESCRIPTION', 'Cash on Pickup');
        $this->sort_order = defined('MODULE_PAYMENT_COP_SORT_ORDER') ? (int)MODULE_PAYMENT_COP_SORT_ORDER : null;
        $this->enabled = (defined('MODULE_PAYMENT_COP_STATUS') && MODULE_PAYMENT_COP_STATUS === 'True');

        // Core loads this file even when the module isn't installed: Modules >
        // Payment reads every file in the folder, and the order page loads it
        // for an old order. Nothing below may touch a setting in that state.
        if ($this->sort_order === null) {
            return;
        }

        if (defined('MODULE_PAYMENT_COP_ORDER_STATUS_ID') && (int)MODULE_PAYMENT_COP_ORDER_STATUS_ID > 0) {
            $this->order_status = (int)MODULE_PAYMENT_COP_ORDER_STATUS_ID;
        }

        if (IS_ADMIN_FLAG === true && !(defined('MODULE_SHIPPING_STOREPICKUP_STATUS') && MODULE_SHIPPING_STOREPICKUP_STATUS === 'True')) {
            $this->description .= $this->text('MODULE_PAYMENT_COP_TEXT_NEEDS_STOREPICKUP', '');
        }

        if (is_object($order)) {
            $this->update_status();
        }
    }

    /**
     * Offered only for pickup, inside the payment zone, on a physical order,
     * and to a One Page Checkout guest only if the store allows it.
     */
    public function update_status()
    {
        global $order;

        // The order page loads the module for an old order; there's no checkout
        // to decide anything about there.
        if (!$this->enabled || IS_ADMIN_FLAG === true) {
            return;
        }

        $pickup = self::isPickup($_SESSION['shipping']['id'] ?? '');
        $eligible = $this->guestAllowed() && $this->inPaymentZone() && ($order->content_type ?? '') === 'physical';
        $_SESSION[self::SESSION_KEY] = ['pickup' => $pickup, 'eligible' => $eligible];

        $this->enabled = ($pickup && $eligible);
    }

    /**
     * Holding goods for someone with no account is a risk a store has to opt
     * in to, so the setting is off by default, and a store that hasn't got the
     * setting yet counts as off. Only One Page Checkout has guests.
     */
    protected function guestAllowed(): bool
    {
        $opc = $_SESSION['opc'] ?? null;
        if (!is_object($opc) || !method_exists($opc, 'isGuestCheckout') || !$opc->isGuestCheckout()) {
            return true;
        }

        return defined('MODULE_PAYMENT_COP_GUEST') && MODULE_PAYMENT_COP_GUEST === 'True';
    }

    /**
     * @param  mixed $shippingId  the id of a shipping selection
     */
    public static function isPickup($shippingId): bool
    {
        return is_string($shippingId) && stripos($shippingId, self::PICKUP_SHIPPING_CODE) !== false;
    }

    /**
     * True when no zone is set, or the delivery address is in it. The address
     * isn't always known (a guest part way through One Page Checkout), and an
     * unknown address isn't held against the customer, the same as core's COD.
     */
    protected function inPaymentZone(): bool
    {
        global $order, $db;

        if ((int)MODULE_PAYMENT_COP_ZONE <= 0 || !isset($order->delivery['country']['id'])) {
            return true;
        }

        $check = $db->Execute(
            "SELECT zone_id
               FROM " . TABLE_ZONES_TO_GEO_ZONES . "
              WHERE geo_zone_id = " . (int)MODULE_PAYMENT_COP_ZONE . "
                AND zone_country_id = " . (int)$order->delivery['country']['id'] . "
              ORDER BY zone_id"
        );
        while (!$check->EOF) {
            if ($check->fields['zone_id'] < 1 || $check->fields['zone_id'] == ($order->delivery['zone_id'] ?? 0)) {
                return true;
            }
            $check->MoveNext();
        }

        return false;
    }

    public function javascript_validation()
    {
        return false;
    }

    public function selection()
    {
        return [
            'id' => $this->code,
            'module' => $this->title,
        ];
    }

    public function pre_confirmation_check()
    {
        return false;
    }

    public function confirmation()
    {
        return false;
    }

    public function process_button()
    {
        return false;
    }

    public function before_process()
    {
        return false;
    }

    public function after_process()
    {
        return false;
    }

    public function get_error()
    {
        return false;
    }

    public function check()
    {
        global $db;

        if (!isset($this->_check)) {
            $check_query = $db->Execute(
                "SELECT configuration_value
                   FROM " . TABLE_CONFIGURATION . "
                  WHERE configuration_key = 'MODULE_PAYMENT_COP_STATUS'"
            );
            $this->_check = $check_query->RecordCount();
        }

        return $this->_check;
    }

    public function install()
    {
        global $db, $messageStack;

        if (defined('MODULE_PAYMENT_COP_STATUS')) {
            $messageStack->add_session($this->text('MODULE_PAYMENT_COP_TEXT_ALREADY_INSTALLED', 'Cash on Pickup is already installed.'), 'error');
            zen_redirect(zen_href_link(FILENAME_MODULES, 'set=payment&module=cop', 'NONSSL'));
            return 'failed';
        }

        $db->Execute(
            "INSERT INTO " . TABLE_CONFIGURATION . "
                (configuration_title, configuration_key, configuration_value, configuration_description,
                 configuration_group_id, sort_order, set_function, date_added)
             VALUES ('Enable Cash on Pickup Module', 'MODULE_PAYMENT_COP_STATUS', 'True',
                     'Do you want to accept cash when customers pick up their orders? It shows at checkout only when Store Pickup is the chosen shipping method.', 6, 1,
                     'zen_cfg_select_option(array(\'True\', \'False\'), ', now())"
        );
        $db->Execute(
            "INSERT INTO " . TABLE_CONFIGURATION . "
                (configuration_title, configuration_key, configuration_value, configuration_description,
                 configuration_group_id, sort_order, use_function, set_function, date_added)
             VALUES ('Payment Zone', 'MODULE_PAYMENT_COP_ZONE', '0',
                     'If a zone is selected, only enable this payment method for that zone.', 6, 2,
                     'zen_get_zone_class_title', 'zen_cfg_pull_down_zone_classes(', now())"
        );
        $db->Execute(
            "INSERT INTO " . TABLE_CONFIGURATION . "
                (configuration_title, configuration_key, configuration_value, configuration_description,
                 configuration_group_id, sort_order, date_added)
             VALUES ('Sort order of display.', 'MODULE_PAYMENT_COP_SORT_ORDER', '0',
                     'Sort order of display. Lowest is displayed first.', 6, 0, now())"
        );
        $db->Execute(
            "INSERT INTO " . TABLE_CONFIGURATION . "
                (configuration_title, configuration_key, configuration_value, configuration_description,
                 configuration_group_id, sort_order, set_function, use_function, date_added)
             VALUES ('Set Order Status', 'MODULE_PAYMENT_COP_ORDER_STATUS_ID', '0',
                     'Set the status of orders made with this payment module to this value', 6, 0,
                     'zen_cfg_pull_down_order_statuses(', 'zen_get_order_status_name', now())"
        );
        $db->Execute(
            "INSERT INTO " . TABLE_CONFIGURATION . "
                (configuration_title, configuration_key, configuration_value, configuration_description,
                 configuration_group_id, sort_order, set_function, date_added)
             VALUES ('Allow Guest Checkout', 'MODULE_PAYMENT_COP_GUEST', 'False',
                     'Do you want One Page Checkout guests to be able to use Cash on Pickup? They have no account with you, so it is off unless you turn it on.', 6, 3,
                     'zen_cfg_select_option(array(\'True\', \'False\'), ', now())"
        );
    }

    public function remove()
    {
        global $db;

        $db->Execute(
            "DELETE FROM " . TABLE_CONFIGURATION . "
              WHERE configuration_key IN ('" . implode("', '", $this->keys()) . "')"
        );
    }

    public function keys()
    {
        return [
            'MODULE_PAYMENT_COP_STATUS',
            'MODULE_PAYMENT_COP_ZONE',
            'MODULE_PAYMENT_COP_GUEST',
            'MODULE_PAYMENT_COP_ORDER_STATUS_ID',
            'MODULE_PAYMENT_COP_SORT_ORDER',
        ];
    }

    /**
     * A language constant, or the fallback: 1.5.8 and 2.0.x have no English
     * fallback for a plugin's language file, and a missing constant is fatal
     * on PHP 8.
     */
    protected function text(string $constant, string $fallback): string
    {
        return defined($constant) ? constant($constant) : $fallback;
    }
}
