# Changelog

## v3.0.0 (2026-11-01)

Rebuilt as an encapsulated plugin for Zen Cart 1.5.8 through 3.0.0 and PHP 7.4 through 8.5. The module code and setting names haven't changed, so settings and past orders carry over.

**Moving from v2.0.0 or earlier:** delete the old files first. The Plugin Manager names any it finds. See docs/INSTALL.md.

Fixed:

- No more `Creation of dynamic property cop::$_check is deprecated` in the logs on PHP 8.2 and later.
- With the module removed but its file still on the server, working on a past Cash on Pickup order from Customers > Orders was a fatal error on PHP 8.
- With a Payment Zone set, customers in that zone saw Cash on Pickup on every shipping method, and pickup orders skipped the zone. It's now Store Pickup only, and the zone applies to it.
- On One Page Checkout, Cash on Pickup didn't appear after a switch to Store Pickup, or go away after a switch off it, until the page was reloaded. The page now refreshes for that one change, with no pop-up.
- The enable setting was titled "Enable Cash On Delivery Module".
- Clicking Install when it's already installed no longer counts as an install.

Added:

- Install, Upgrade and Uninstall through the Plugin Manager, with ReadMe, Settings, Forum Thread and GitHub buttons.
- Uninstall directions, which earlier versions never had.
- A note in the Modules > Payment panel when Store Pickup shipping isn't enabled.
- An Allow Guest Checkout setting, off by default. One Page Checkout guests don't see Cash on Pickup unless you turn it on. If guests were using it on v2.0.0, turn this on after you move.

Removed:

- The admin startup files, which ran on every admin page, and the empty Cash on Pickup page they added to the Configuration menu. Installing cleans those leftovers out of the database.

## v2.0.0 (2026-01-17)

- Modernized for current PHP by dbltoe.

## v1.2.0 (2019-11-08)

- Automatic installer and code formatting by Design75 (Zen4All).
