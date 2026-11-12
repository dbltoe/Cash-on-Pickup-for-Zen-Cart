# Cash on Pickup

Version 3.0.1 · Zen Cart 1.5.8 through 3.0.0 · PHP 7.4 through 8.5 · GPL v3 · by My Zen Cart Host (dbltoe)

Lets a customer order online and pay cash when they pick the order up. It's offered only when they choose Store Pickup shipping, on the standard checkout and on One Page Checkout.

## What it does

Cash on Pickup lets a customer place an order online and pay cash when they pick it up at your location. It's offered only when the customer chooses Store Pickup shipping, so nobody can pick it for an order you're mailing.

The module takes no payment itself. The order comes through with Cash on Pickup as its payment method, and you collect the money at the counter.

## Requirements

- Zen Cart 1.5.8 through 3.0.0, on PHP 7.4 through 8.5, within the PHP range your Zen Cart release supports.
- The **Store Pickup** shipping module that comes with Zen Cart, enabled under Modules > Shipping. The module's panel under Modules > Payment tells you if it isn't.

## Installing

1. Upload the `zc_plugins` folder from the package to your store's main folder. The plugin lands at `zc_plugins/CashOnPickup/v3.0.1/`.
2. **On Zen Cart 1.5.8 through 2.1.x only,** also upload the `includes` folder from the package's `for_zen_cart_1.5.8_to_2.1.x` folder. Those two bridge files are how those releases find the module. The Plugin Manager tells you if they're missing.
3. Go to Modules > Plugin Manager, select **Cash on Pickup** and click Install.
4. Click the **Settings** button in its panel, or go to Modules > Payment and select Cash on Pickup. Click Install and set it up.

Once it's installed, a superuser can open this guide from the **ReadMe** button in Plugin Manager.

## Moving from v2.0.0 or earlier

Earlier versions weren't plugins, so they don't show in the Plugin Manager, and they can't run beside this one. Your settings and past orders carry over, because the module's code and setting names haven't changed. The one new setting, Allow Guest Checkout, starts off, so if One Page Checkout guests were using Cash on Pickup, turn it on after you move.

First delete these files, the admin ones first:

- `YOUR_ADMIN/includes/auto_loaders/config.cash_on_pickup.php`
- `YOUR_ADMIN/includes/init_includes/init_cash_on_pickup.php`
- `YOUR_ADMIN/includes/installers/cash_on_pickup/` (the whole folder)
- `YOUR_ADMIN/includes/languages/english/extra_definitions/lang.cash_on_pickup.php` (or `cash_on_pickup.php` on a very old install)
- `includes/modules/payment/cop.php`
- `includes/languages/english/modules/payment/lang.cop.php` (or `cop.php` on a very old install)

Then follow Installing. On 1.5.8 through 2.1.x the bridge files go back in the last two places above, under the same names.

The Plugin Manager refuses the install and names the files if any are still there. Once it installs, it removes the old version's leftovers from the database: the Version row, its empty settings group and its Configuration menu page.

## Settings

All five are under Modules > Payment > Cash on Pickup.

| Setting | What it does |
| --- | --- |
| Enable Cash on Pickup Module | True offers it at checkout when the order qualifies. |
| Payment Zone | If you pick a zone, only customers whose delivery address is in that zone see it. Leave it at --none-- for everyone. |
| Allow Guest Checkout | Off by default. True lets One Page Checkout guests use it. They have no account with you, so you'd be holding goods for someone you can't look up. |
| Sort order of display. | Where it sits in the list of payment methods. Lowest comes first. |
| Set Order Status | The status new orders get. A separate "awaiting pickup" status can help you see what's still unpaid. |

It shows at checkout when all of these are true: it's enabled, Store Pickup is the chosen shipping method, the address passes the Payment Zone, the whole order is physical goods, and the customer isn't a guest unless you've allowed guests. An order with any download in it doesn't qualify, the same as Zen Cart's own Cash on Delivery.

## One Page Checkout

One Page Checkout changes the shipping method without rebuilding the payment list. So when a customer switches to or from Store Pickup, this plugin asks One Page Checkout to refresh the page, and the list updates. Their choices stay, but anything already typed into a card form has to be typed again. It doesn't happen on any other shipping change, or on orders that can't use Cash on Pickup anyway.

> Guests don't see Cash on Pickup unless you set Allow Guest Checkout to True. If they still don't, check One Page Checkout's own list of payment methods that guests can't use, under Configuration > One Page Checkout. If `cop` is in that list, it wins.

## Troubleshooting

| What you see | Why, and what to do |
| --- | --- |
| It doesn't show at checkout | Check the five conditions under Settings. Most often Store Pickup isn't enabled, or isn't the selected shipping method. |
| Plugin Manager refuses the install | Its message names what's missing or what's in the way. See Moving from v2.0.0 or earlier. |
| A log entry saying `Creation of dynamic property cop::$_check is deprecated` | That's v2.0.0 on PHP 8.2 or later, and it's harmless. It shows even after you click Remove, because Remove keeps the files. Version 3.0.0 and later fix it. |
| Under Modules > Payment, a title saying the plugin folder is missing | The bridge files are there but `zc_plugins/CashOnPickup` isn't. Put the folder back or delete the two files the title names. |

## Removing and uninstalling

- **Remove** under Modules > Payment takes it off your checkout and deletes its settings. The plugin stays, so you can install it again.
- **Uninstall** in the Plugin Manager does the same if you haven't already.
- To take it off the server after that, use the Plugin Manager's Clean Up, or delete the `zc_plugins/CashOnPickup` folder. If you uploaded the bridge files, delete those two as well.

Past orders aren't touched. They keep Cash on Pickup as their payment method.

## Support

Support is in the forum thread (https://www.zen-cart.com/threads/207082?page=1#post-1344942). The source is on GitHub (https://github.com/dbltoe/Cash-on-Pickup-for-Zen-Cart).

The download's `docs` folder has more on installing, the settings, customizing the wording and compatibility.

## Changelog

See `CHANGELOG.md`. Version 3.0.1 moves the plugin to the GNU General Public License, version 3, and replaces the old HTML readme with this guide, opened from the ReadMe button in Plugin Manager.

## License

Cash on Pickup is free software under the GNU General Public License, version 3 (https://www.gnu.org/licenses/gpl-3.0.html); the full text is in `LICENSE`. Copyright © 2026 My Zen Cart Host (dbltoe).
