#!/bin/bash

# initiate phpbrew
export PHPBREW_HOME=/opt/phpbrew
export PHPBREW_ROOT=/opt/phpbrew
/usr/bin/phpbrew init >/dev/null
source ${PHPBREW_ROOT}/bashrc
