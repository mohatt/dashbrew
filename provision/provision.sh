#!/bin/bash

export DEBIAN_FRONTEND=noninteractive
export VAGRANT_SSH_USERNAME=$(echo "$1")

function run_scripts () {
    local dir=$1
    local dir_path="/vagrant/provision/${dir}"

    if [ -d $dir_path && "$(find ${dir_path} -maxdepth 1 -type f -name '*.sh')"]; then
        echo "Running ${dir}-provisioning scripts in provision/${dir}/"

        find $dir_path -maxdepth 1 -type f -name '*.sh' | sort | while read FILENAME; do
            chmod +x "${FILENAME}"
            # /bin/bash "${FILENAME}"
            ${FILENAME}
        done

        echo "Finished running ${dir}-provisioning scripts."
    fi
}

# Run the pre-provisioning scripts
run_scripts pre

# Run the main provisioning script
/vagrant/provision/main/init.sh

# Run the post-provisioning scripts
run_scripts post