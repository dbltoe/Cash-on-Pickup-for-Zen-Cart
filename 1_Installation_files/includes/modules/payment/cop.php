<?php
/**
 * COD (Cash on Pickup) Payment Module
 *
 * @package paymentMethod
 * @copyright Copyright 2016 Zen4All
 * @copyright Portions Copyright 2003-2018 Zen Cart Development Team
 * @copyright Portions Copyright 2003 osCommerce
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: 2.0.0 Author: dbltoe - PHP 8.4 compatible update
 */
class cop
{
    public string $code;
    public string $title;
    public string $description;
    public bool $enabled;
    public ?int $sort_order = null;
    public ?int $order_status = null;

    private int $_check = 0; // cache the check result (private property)

    public function __construct()
    {
        global $order;

        $this->code        = 'cop';
        $this->title       = MODULE_PAYMENT_COP_TEXT_TITLE;
        $this->description = MODULE_PAYMENT_COP_TEXT_DESCRIPTION;
        $this->sort_order  = defined('MODULE_PAYMENT_COP_SORT_ORDER') ? (int)MODULE_PAYMENT_COP_SORT_ORDER : null;
        $this->enabled     = (defined('MODULE_PAYMENT_COP_STATUS') && MODULE_PAYMENT_COP_STATUS === 'True');

        if (defined('MODULE_PAYMENT_COP_ORDER_STATUS_ID') && (int)MODULE_PAYMENT_COP_ORDER_STATUS_ID > 0) {
            $this->order_status = (int)MODULE_PAYMENT_COP_ORDER_STATUS_ID;
        }

        if (is_object($order)) {
            $this->update_status();
        }
    }

    public function update_status(): void
    {
        global $order, $db;

        if (!$this->enabled) {
            return;
        }

        // Only allow for storepickup shipping
        if (stripos($_SESSION['shipping']['id'] ?? '', 'storepickup') === false) {
            $this->enabled = false;
            return;
        }

        // Zone restriction check
        if ((int)MODULE_PAYMENT_COP_ZONE > 0) {
            $check_flag = false;
            $check = $db->Execute(
                "SELECT zone_id
                 FROM " . TABLE_ZONES_TO_GEO_ZONES . "
                 WHERE geo_zone_id = " . (int)MODULE_PAYMENT_COP_ZONE . "
                 AND zone_country_id = " . (int)$order->delivery['country']['id'] . "
                 ORDER BY zone_id"
            );

            while (!$check->EOF) {
                if ($check->fields['zone_id'] < 1 || $check->fields['zone_id'] == $order->delivery['zone_id']) {
                    $check_flag = true;
                    break;
                }
                $check->MoveNext();
            }

            if (!$check_flag) {
                $this->enabled = false;
                return;
            }
        }

        // Disable if order contains only virtual products
        if ($order->content_type !== 'physical') {
            $this->enabled = false;
        }
    }

    public function javascript_validation(): bool
    {
        return false;
    }

    public function selection(): array
    {
        return [
            'id'     => $this->code,
            'module' => $this->title
        ];
    }

    public function pre_confirmation_check(): bool
    {
        return false;
    }

    public function confirmation(): bool
    {
        return false;
    }

    public function process_button(): bool
    {
        return false;
    }

    public function before_process(): bool
    {
        return false;
    }

    public function after_process(): bool
    {
        return false;
    }

    public function get_error(): bool
    {
        return false;
    }

    /**
     * Check if this payment module is installed
     * Matches Zen Cart base payment class signature to avoid PHP 8.4 deprecation
     *
     * @param mixed|null $module Optional parameter (unused) to match parent signature
     * @return int 1 if installed, 0 otherwise
     */
    public function check($module = null): int
    {
        global $db;

        if (!isset($this->_check) || $this->_check === 0) {
            $check_query = $db->Execute(
                "SELECT configuration_value
                 FROM " . TABLE_CONFIGURATION . "
                 WHERE configuration_key = 'MODULE_PAYMENT_COP_STATUS'"
            );

            $this->_check = $check_query->RecordCount() > 0 ? 1 : 0;
        }

        return $this->_check;
    }

    public function install(): void
    {
        global $db, $messageStack;

        if (defined('MODULE_PAYMENT_COP_STATUS')) {
            $messageStack->add_session('COP module already installed.', 'error');
            zen_redirect(zen_href_link(FILENAME_MODULES, 'set=payment&module=cop', 'NONSSL'));
            return;
        }

        $db->Execute(
            "INSERT INTO " . TABLE_CONFIGURATION . "
             (configuration_title, configuration_key, configuration_value, configuration)";
