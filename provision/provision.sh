#!/bin/bash

export DEBIAN_FRONTEND=noninteractive

# Run the main provisioning script
chmod +x /vagrant/provision/main/dashbrew
/vagrant/provision/main/dashbrew
