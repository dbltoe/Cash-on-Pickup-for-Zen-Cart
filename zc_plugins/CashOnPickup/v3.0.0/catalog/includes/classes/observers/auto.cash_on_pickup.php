<?php
/**
 * Cash on Pickup: keeps One Page Checkout's payment list honest.
 *
 * OPC changes the shipping method by AJAX and updates the totals, but it never
 * rebuilds the payment block, so a payment method that depends on shipping
 * neither appears when the customer switches to Store Pickup nor goes away
 * when they switch off it. OPC's 'reload' status is its own answer to a page
 * that's out of step, so this asks for one, and only when the change crosses
 * in or out of pickup on an order that could use this module.
 *
 * It sets no message to go with the status: OPC 2.6.3 alerts a reload's message
 * twice, and the payment list changing says it all.
 *
 * Discovered by core's init_observers; the file and class names are tied
 * together, and there's no auto_loader entry to go with them. The notifier
 * doesn't exist without OPC, so this does nothing on the standard checkout.
 *
 * @package  CashOnPickup
 * @license  https://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU Public License V2.0
 */

if (!defined('IS_ADMIN_FLAG')) {
    die('Illegal Access');
}

class zcObserverCashOnPickup extends base
{
    public function __construct()
    {
        if (defined('MODULE_PAYMENT_COP_STATUS') && MODULE_PAYMENT_COP_STATUS === 'True') {
            $this->attach($this, ['NOTIFY_AJAX_OPC_UPDATE_SHIPPING']);
        }
    }

    /**
     * @param  mixed  $callingClass
     * @param  string $eventID
     * @param  array  $shippingElements  [module, method] of the new selection
     * @param  string $status            OPC's response status
     */
    public function update(&$callingClass, $eventID, $shippingElements, &$status = null)
    {
        $page = $_SESSION['cash_on_pickup'] ?? null;
        if ($status !== 'ok' || !is_array($page) || empty($page['eligible'])) {
            return;
        }

        // The class isn't loaded on this request, because OPC builds no payment
        // modules here, so the test is cop::isPickup()'s, on the session's id.
        $pickup = stripos((string)($_SESSION['shipping']['id'] ?? ''), 'storepickup') !== false;
        if ($pickup !== !empty($page['pickup'])) {
            $status = 'reload';
        }
    }
}
