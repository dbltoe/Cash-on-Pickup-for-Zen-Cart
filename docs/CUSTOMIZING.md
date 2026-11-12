# Customizing

## Text and other languages

All the wording is in `zc_plugins/CashOnPickup/v3.0.1/catalog/includes/languages/english/modules/payment/lang.cop.php`. It returns an array of definitions.

To add a language, copy it to the matching folder, such as `.../languages/spanish/modules/payment/lang.cop.php`, and translate the values. Keep the keys as they are.

On Zen Cart 1.5.8 through 2.1.x, also copy the language bridge file into that language's core folder, `includes/languages/spanish/modules/payment/lang.cop.php`. Those releases read the language file from there. The bridge returns the plugin's English either way, so to use your translation there, make that copy your translated file instead.

| Key | Where it shows |
|---|---|
| `MODULE_PAYMENT_COP_TEXT_TITLE` | The payment method's name at checkout, and on the order |
| `MODULE_PAYMENT_COP_TEXT_DESCRIPTION` | The panel under Modules > Payment |
| `MODULE_PAYMENT_COP_TEXT_NEEDS_STOREPICKUP` | Added to that panel when Store Pickup shipping isn't enabled |
| `MODULE_PAYMENT_COP_TEXT_ALREADY_INSTALLED` | The admin message if it's installed twice |

## A different pickup module

The module looks for `storepickup` in the chosen shipping method's id, in `cop::PICKUP_SHIPPING_CODE` and in the One Page Checkout observer at `catalog/includes/classes/observers/auto.cash_on_pickup.php`. A shipping module of your own whose code contains `storepickup` works without changes. For any other name, change both places, and redo the change after each update.
