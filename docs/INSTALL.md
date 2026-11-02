# Installing and Uninstalling

## New install

1. Upload `zc_plugins` to your store's main folder.
2. **Zen Cart 1.5.8 through 2.1.x only:** also upload the `includes` folder from `for_zen_cart_1.5.8_to_2.1.x`. It adds two small bridge files, and no core file changes. The Plugin Manager tells you if they're missing.
3. Go to Modules > Plugin Manager, select **Cash on Pickup** and click Install.
4. Click **Settings** in its panel, then click Install under Modules > Payment and set it up.
5. Make sure the **Store Pickup** shipping module is enabled under Modules > Shipping.

## Moving from v2.0.0 or earlier

Earlier versions weren't plugins, and they can't run beside this one. Your settings and past orders carry over because the module's code and setting names haven't changed. The one new setting, Allow Guest Checkout, starts off, so if One Page Checkout guests were using Cash on Pickup, turn it on after you move.

Delete these files, the admin ones first, because they run on every admin page:

- `YOUR_ADMIN/includes/auto_loaders/config.cash_on_pickup.php`
- `YOUR_ADMIN/includes/init_includes/init_cash_on_pickup.php`
- `YOUR_ADMIN/includes/installers/cash_on_pickup/` (the whole folder)
- `YOUR_ADMIN/includes/languages/english/extra_definitions/lang.cash_on_pickup.php` (or `cash_on_pickup.php`)
- `includes/modules/payment/cop.php`
- `includes/languages/english/modules/payment/lang.cop.php` (or `cop.php`)

Then follow the new install above. On 1.5.8 through 2.1.x, the bridge files go back in the last two places under the same names.

The Plugin Manager refuses the install and names the files if any are still there. Once it installs, it clears the old Version row, its empty settings group and its Configuration menu page from the database.

## Removing

- **Remove** under Modules > Payment takes it off your checkout and deletes its settings. The plugin stays.
- **Uninstall** in the Plugin Manager does the same if you haven't already.
- To take it off the server, use the Plugin Manager's Clean Up or delete `zc_plugins/CashOnPickup`, and delete the two bridge files if you uploaded them.

Past orders keep Cash on Pickup as their payment method.

## Removing v2.0.0 without moving to this version

Remove it under Modules > Payment, delete the six items in the list above, then paste this into Tools > Install SQL Patches:

```sql
DELETE FROM configuration WHERE configuration_key = 'CASH_ON_PICKUP_VERSION';
DELETE FROM configuration_group WHERE configuration_group_title = 'Cash on Pickup';
DELETE FROM admin_pages WHERE page_key = 'configCashOnPickup';
```
