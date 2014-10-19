#!/bin/bash

DIR="$( cd "$( dirname "${BASH_SOURCE[0]}" )" && pwd )"

source ${DIR}/init.sh

BUILD=$(echo "$1")
EXTNAME=$(echo "$2")
EXTVERSION=$(echo "$3")

phpbrew use ${BUILD}
phpbrew ext install ${EXTNAME} ${EXTVERSION} 2>&1
