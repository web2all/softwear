# Web2All: Softwear stock synchronisation #

This is a Magento 2 extension for synchronising the stocklevels from Softwear to Magento.

Only useful for Softwear customers. This is software created by Web2All B.V. and put in the public domain. Web2All no longer actively supports this software, feel free to fork.

For licencing see LICENSE.txt.

This module uses the Softwear Web-API ([https://help.softwear.nl/display/SWHELP/Softwear+Web-API](https://help.softwear.nl/display/SWHELP/Softwear+Web-API)). Latest tested version of the Softwear API is 1.15 (SWAPI).

## Install ##

To install this magento module, copy it to the Magento installation and run `bin/magento setup:upgrade`. The code will be in `/app/code/Web2All/Softwear/`.

After installation, configure it in the Store configuration under `Web2All Extensions` -> `Softwear Sync`.
