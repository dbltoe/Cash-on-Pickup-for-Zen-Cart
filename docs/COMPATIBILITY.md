# Compatibility

One codebase for Zen Cart 1.5.8 through 3.0.0 and PHP 7.4 through 8.5, within the PHP range each Zen Cart release supports.

## Tested

Each run below was a full pass on a real store: the v2.0.0 package installed, v3.0.0 refused until it was deleted, then installed with the old settings kept, an order placed through Store Pickup and paid with Cash on Pickup, that order opened in admin, Remove, upgrade, Uninstall, and the logs checked.

| Zen Cart | PHP | Bridge files |
|---|---|---|
| 1.5.8a | 7.4, 8.0 | Yes |
| 2.0.1 | 8.1, 8.3 | Yes |
| 2.1.0 | 8.0, 8.3 | Yes |
| 2.2.2 | 8.2, 8.5 | No |
| 2.3 branch | 8.3, 8.5 | No |
| 3.0.0-dev | 8.4, 8.5 | No |

One Page Checkout 2.6.3 was tested on Zen Cart 2.2.2 and PHP 8.2 in a browser, through to placed orders: one by a customer with an account, and one by a guest with Allow Guest Checkout on. With it off, the guest never saw Cash on Pickup.

It was also run on Linux, on a CloudLinux server with PHP 8.4, MariaDB 10.5, Zen Cart 2.2.2 and One Page Checkout 2.7.0: the refusal, Install, the guest switch both ways and the refresh in a browser as far as the Confirm step, and Uninstall. Every file also lints clean there on PHP 7.4 through 8.5.

The automated checks also run on every PHP from 7.4 through 8.5.

## Why the bridge files

| Zen Cart | Payment class finds plugin modules | Order page finds plugin modules | Bridge files |
|---|---|---|---|
| 1.5.8, 2.0.x | No | No | Needed |
| 2.1.x | Yes | No | Needed |
| 2.2.0 and later | Yes | Yes | Not needed |

## What it depends on

- Zen Cart's own Store Pickup shipping module, or a shipping module whose code contains `storepickup`.
- One Page Checkout's `NOTIFY_AJAX_OPC_UPDATE_SHIPPING` notifier and its `reload` status, for the page refresh there. Without One Page Checkout, nothing uses them.
- It adds no tables, and it changes no core files.
