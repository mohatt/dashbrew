#!/bin/bash

DIR="$( cd "$( dirname "${BASH_SOURCE[0]}" )" && pwd )"

source $DIR/init.sh

PHPVERSION=$(echo "$1")
EXTNAME=$(echo "$2")

phpbrew switch ${PHPVERSION}
phpbrew ext install ${EXTNAME}
