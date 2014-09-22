#!/bin/bash

DIR="$( cd "$( dirname "${BASH_SOURCE[0]}" )" && pwd )"

source $DIR/phpbrew.init.sh

phpbrew off
phpbrew switch-off
