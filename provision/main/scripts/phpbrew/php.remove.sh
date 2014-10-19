#!/bin/bash

DIR="$( cd "$( dirname "${BASH_SOURCE[0]}" )" && pwd )"

source ${DIR}/init.sh

BUILD=$(echo "$1")

phpbrew remove ${BUILD}
