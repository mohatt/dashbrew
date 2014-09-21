#!/bin/bash

DIR="$( cd "$( dirname "${BASH_SOURCE[0]}" )" && pwd )"

source $DIR/phpbrew.init.sh

PHPVERSION=$(echo "$1")

phpbrew remove ${PHPVERSION}
