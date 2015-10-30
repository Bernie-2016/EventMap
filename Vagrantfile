# -*- mode: ruby -*-
# vi: set ft=ruby :

Vagrant.configure("2") do |config|
  config.vm.synced_folder "./", "/var/www/html"
  config.vm.box = "ubuntu/vivid64"
  config.vm.network :forwarded_port, guest: 80, host: 9000
  config.vm.provider "virtualbox" do |v|
    v.cpus = 2
    v.memory = 1024
  end

  config.vm.provision "shell", inline: <<-SHELL
    sudo apt-get update
    DEBIAN_FRONTEND=noninteractive sudo -E apt-get install -y apache2
  SHELL
end
