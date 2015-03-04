#!/bin/bash

function run_scripts() {
    local dir=$1
    local dir_path="/vagrant/provision/${dir}"

    if [[ -d ${dir_path} && "$(find ${dir_path} -maxdepth 1 -type f -name '*.sh')" ]]; then
        echo "[Info] Running ${dir}-provisioning scripts in provision/${dir}"

        find ${dir_path} -maxdepth 1 -type f -name '*.sh' | sort | while read FILENAME; do
            chmod 755 "${FILENAME}"
            # /bin/bash "${FILENAME}"
            ${FILENAME}
        done

        echo "[Info] Finished running ${dir}-provisioning scripts."
    fi
}

# Run the pre-provisioning scripts
run_scripts pre

# Run dashbrew provisioner
chmod 755 /vagrant/provision/main/dashbrew
/vagrant/provision/main/dashbrew provision

# Run the post-provisioning scripts
run_scripts post
