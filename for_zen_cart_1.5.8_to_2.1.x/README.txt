Cash on Pickup -- files for Zen Cart 1.5.8 through 2.1.x

Zen Cart 2.2.0 and later load this payment module straight from zc_plugins and
need nothing from this folder.

Zen Cart 1.5.8, 1.5.8a, 2.0.0, 2.0.1 and 2.1.0 look for it in
includes/modules/payment, so on those releases upload the "includes" folder
from here to the store as well, alongside the zc_plugins folder. It adds two
small files that hand off to the plugin:

  includes/modules/payment/cop.php
  includes/languages/english/modules/payment/lang.cop.php

No core file is changed. If you're moving from Cash on Pickup v2.0.0 or
earlier, these replace the old files of the same names. The Plugin Manager
refuses the install on those releases until both files are present, and says
so. After an upgrade to 2.2.0 or later the files are ignored and can be deleted
or left alone.
