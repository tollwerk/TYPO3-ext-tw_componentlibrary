#!/bin/bash

cd /path/to/your/fractal/instance

# Do whatever necessary to re-initialize Fractal in your context ...
/usr/bin/fractal update-typo3 > /path/to/your/log 2>1 && sudo /etc/init.d/fractal restart
