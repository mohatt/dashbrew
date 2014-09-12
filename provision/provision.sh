#!/bin/bash

export DEBIAN_FRONTEND=noninteractive
export VAGRANT_SSH_USERNAME=$(echo "$1")

# Run the main provisioning script
chmod +x /vagrant/provision/main/init
/vagrant/provision/main/init
