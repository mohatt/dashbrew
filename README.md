# :warning: Warning

This project is no longer maintained. I've stopped using the workflow that made it relevant to me, and Vagrant synced folders still has some unresolved issues with VirtualBox provider especially on Windows hosts, also PHPBrew is not quite stable yet and not actively maintained. Thanks!

![Dashbrew Logo](https://raw.githubusercontent.com/mdkholy/mdkholy.github.io/master/assets/img/etc/dashbrew-logo-640.png)

## What is Dashbrew?

Dashbrew is a [Vagrant](http://www.vagrantup.com/) build that aims at providing a powerful PHP development environment that can be used as a replacement for local development stacks such as MAMP, XAMPP, and others. It provides an easy way to manage, organize and develop PHP projects and comes with a unique dashboard that allows managing various environment aspects. It also comes preinstalled with [all software and tools](https://github.com/mdkholy/dashbrew-basebox#installed-components) needed to start developing right out of the box.

Dashbrew makes use of [phpbrew](https://github.com/phpbrew/phpbrew) &mdash; the wonderful PHP version management utility &mdash; to allow developing both web and command-line projects on different PHP versions and configurations on the same development environment.

## How it works?

### The Vagrant Base box

Dashbrew uses a pre-configured vagrant box that runs Ubuntu 14.04.1 LTS (Trusty Tahr) and comes preinstalled with [all components](https://github.com/mdkholy/dashbrew-basebox#installed-components) needed to run Dashbrew environment (e.g. monit, php, apache, mysql, phpbrew). The base box is built using [Packer](https://www.packer.io/).

For more information on the Packer template used to build the base box and the components that comes pre-installed in it, please visit the [dashbrew-basebox](https://github.com/mdkholy/dashbrew-basebox) repository.

### Dashbrew provisioner

The Vagrant virtual machine is provisioned using a custom provisioning system (i.e. Dashbrew Provisioner) written in pure PHP. This eliminates the need to learn complex provisioning systems (e.g. Puppet or Chef) in order to extend or modify the provisioning process.

Dashbrew provisioner is a PHP command-line application built on top of Symfony components and is used to perform several tasks on the virtual machine based on your configurations. Examples for these tasks include:

* Installing system software packages, ruby gems or npm modules
* Installing PHP versions and extensions
* Managing projects and apache virtual hosts

## Getting Started

### System Requirements

Before launching your Dashbrew environment, you must install VirtualBox and Vagrant. Both of these software packages provide easy-to-use visual installers for all popular operating systems.

* [Vagrant >= 1.6.5](http://www.vagrantup.com/)
* [VirtualBox 4.3.x](https://www.virtualbox.org/)
	* Note that installing Virtualbox 5.x is *strongly* recommended for better performance.
* [Vagrant Hosts Provisioner plugin](https://github.com/mdkholy/vagrant-hosts-provisioner) for managing the /etc/hosts file of both the host and guest machines.
	* ``$ vagrant plugin install vagrant-hosts-provisioner``

### Adding The Vagrant Box

Once VirtualBox and Vagrant have been installed, you should add the ``mdkholy/dashbrew`` box to your Vagrant installation using the following command in your terminal. It will take a few minutes to download the box, depending on your Internet connection speed:

```
$ vagrant box add mdkholy/dashbrew
```

### Clone The Dashbrew Repository

Once the box has been added to your Vagrant installation, you should clone this repository. Consider cloning the repository into a central directory where you keep all of your projects, as Dashbrew will serve as the host to all of your PHP projects.

```
$ git clone --recursive git://github.com/mdkholy/dashbrew.git
```

***Note:*** The ``--recursive`` is required to clone the repository with all its dependencies (i.e. git submodules).  

### The First Vagrant Up

Once you have cloned the Dashbrew Repository, start the Vagrant environment by running ``vagrant up`` command from the Dashbrew directory in your terminal. Vagrant will setup the virtual machine and boot it for the first time. This could take a while on the first run.

### Launch the Dashbrew Dashboard

Once the ``vagrant up`` command is finished, you can now launch the Dashbrew Dashboard by visiting [http://dashbrew.dev/](http://dashbrew.dev/) in your browser. Here is a screenshot of what it looks like.

![Dashbrew Dashboard](https://raw.githubusercontent.com/mdkholy/mdkholy.github.io/master/assets/img/etc/dashbrew-dashboard-1024.png)

## Configuration

Dashbrew environment can be configured via a configuration file located in ``config/environment.yaml`` (***Note:*** This file is not included in the repository by default, so you will need to create it). This file allows managing different components on your environment such as php versions, system packages, ruby gems, npm modules, etc.

A sample config file is located in ``config/environment.yaml.sample``, if you would like to use it, just rename it to ``environment.yaml``.

### PHP builds

Dashbrew allows having multiple PHP versions installed on the same environment (thanks to [phpbrew](https://github.com/phpbrew/phpbrew)). A PHP version installation is refered to as a PHP build and every build must have a unique name to identify it.

You can define as many PHP builds as you may like in the environment configuration file under ``php::builds`` property, you can even have multiple builds for the same PHP version but with different extensions and configurations. 

Here is a sample ``php::builds`` definition:
```
php::builds:
  5.3.29:
    variants: dev
    extensions:
      xdebug:
        enabled: true
        version: stable
    fpm:
      port: 9002
      autostart: true
  5.6.0:
    default: true
    variants: dev
    extensions:
      xdebug:
        enabled: true
        version: stable
      xhprof:
        enabled: true
        version: latest
    fpm:
      port: 9003
      autostart: true
```

For more information on how to configure Dashbrew environment, please visit [the wiki page](https://github.com/mdkholy/dashbrew/wiki/configuration).

## Adding Projects

In order to add a project to Dashbrew, all your project files needs to be under ``public/`` directory which is the root directory of the apache web server. 

Every project needs a project configurations file in order to be added to Dashbrew. This file needs to be created in the project's root directory with the name ``.dashbrew`` and must be a valid YAML file. This file allows configuring different project aspects such as the php version it runs on and the apache virtual host entry needed for launching it.

So lets say you have the following project structure (under ``public/`` directory):
```
myproject/
|-- assets/
|-- lib/
|-- index.php
```

Create a new file called ``.dashbrew`` under ``myproject/`` directory like this:
```
myproject/
|-- assets/
|-- lib/
|-- .dashbrew
|-- index.php
```

Edit ``.dashbrew`` file with the following configurations:
```
---
myproject:
  title: My Project
  php:
    build: system
  vhost:
    servername: myproject.dev
    serveraliases:
      - www.myproject.dev
    ssl: true
```

Run ``vagrant provision`` so that Dashbrew can find your project and make the nesseccary changes. Then visit [http://myproject.dev/](http://myproject.dev/) in your browser and you should see the output of the ``index.php``.

What we done here is that we added a new project that:

* runs on system php
* can be accessed via ``myproject.dev`` or ``www.myproject.dev``
* has SSL enabled so that it could be accessed using https

For more information on adding projects, please visit [the wiki page](https://github.com/mdkholy/dashbrew/wiki/Projects).

## Shared Configuration Files

These are configuration files primarily used by software installed on the guest machine and are shared with the host machine in order to facilitate editing them. Examples of these files are the Apache configuration file, MySQL configuration file and PHP INI files for each PHP build.

Dashbrew uses a bi-directional synchronization logic to keep the shared configuration files in sync between the host and guest machines. Synchronization of these files is done during [the provisioning process](#applying-your-changes).

All shared configuration files are located under ``config/`` directory and are organized in subfolders according their relevant software.

Here is a [list of the currently supported configuration files](https://github.com/mdkholy/dashbrew/wiki/SharedConfigurationFiles).

## Applying Your Changes

Whenever you make changes to the Dashbrew environment (e.g. changing a configuration file, adding/removing projects), you need to run ``vagrant provision`` in order to apply your changes. Dashbrew provisioner will provide you with useful info and debug (if enabled) messages during the provisioning process.

## Default Environment Information

### Virtual Machine

* IP Address: ``192.168.10.10``
* Base Memory: ``1024``
* CPUs: ``1``


### SSH

* Port: ``22``
* Username: ``vagrant``
* Password: ``vagrant``
* Private Key: *The default insecure private key that ships with Vagrant*


### MySQL

* Port: ``3306``
* Root Username: ``root``
* Root Password: ``root``

## Need Help?

* Don't hesitate to open a new issue on GitHub if you run into trouble.
* The [Dashbrew Wiki](https://github.com/mdkholy/dashbrew/wiki) also contains documentation that may help.

## Want to help?

If you would like to help, take a look at the list of issues. Fork the project, create a feature branch, and send us a pull request.

I created this for my own development purposes, but I welcome pull requests and suggestions to turn this into a useful resource for the entire community.
