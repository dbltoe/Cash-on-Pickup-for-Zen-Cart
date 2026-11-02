# Settings

All five live under Modules > Payment > Cash on Pickup.

| Setting | Default | What it does |
|---|---|---|
| Enable Cash on Pickup Module | True | True offers it at checkout when the order qualifies. |
| Payment Zone | --none-- | If you pick a zone, only customers whose delivery address is in that zone see it. |
| Allow Guest Checkout | False | True lets One Page Checkout guests use it. They have no account with you, so it's off unless you turn it on. |
| Sort order of display. | 0 | Where it sits in the list of payment methods. Lowest comes first. |
| Set Order Status | default | The status new orders get. A separate "awaiting pickup" status can help you see what's still unpaid. |

## When it shows at checkout

All of these have to be true:

1. It's enabled.
2. Store Pickup is the chosen shipping method.
3. The delivery address passes the Payment Zone. If the address isn't known yet, it passes, the same as Zen Cart's own Cash on Delivery.
4. The whole order is physical goods. An order with a download in it doesn't qualify.
5. The customer isn't a One Page Checkout guest, unless Allow Guest Checkout is True.

## One Page Checkout

One Page Checkout changes shipping without rebuilding the payment list, so the plugin asks it to refresh the page when a customer switches to or from Store Pickup, and the list updates. Anything already typed into a card form has to be typed again. Nothing happens on other shipping changes, or on orders that can't use Cash on Pickup.

One Page Checkout also has its own list of payment methods that guests can't use, under Configuration > One Page Checkout. If `cop` is in that list, it wins over Allow Guest Checkout.
