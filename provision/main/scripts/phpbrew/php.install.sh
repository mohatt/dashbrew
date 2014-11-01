#!/bin/bash

DIR="$( cd "$( dirname "${BASH_SOURCE[0]}" )" && pwd )"

source ${DIR}/init.sh

BUILD=$(echo "$1")
BUILDDIR="/opt/phpbrew/php/${BUILD}"
VERSION=$(echo "$2")
VARIANTS=$(echo "$3")

phpbrew install --alias "${BUILD}" ${VERSION} ${VARIANTS}
