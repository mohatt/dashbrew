#!/bin/bash

DIR="$( cd "$( dirname "${BASH_SOURCE[0]}" )" && pwd )"

source $DIR/init.sh

PHPVERSION=$(echo "$1")
VARIANTS=$(echo "$2")

phpbrew install ${PHPVERSION} ${VARIANTS} 2>&1
mkdir -p "/opt/phpbrew/php/php-${PHPVERSION}/var/db"
