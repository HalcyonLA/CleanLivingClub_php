# Vagrantfile API/syntax version. Don't touch unless you know what you're doing!
VAGRANTFILE_API_VERSION = "2"

Vagrant.configure(VAGRANTFILE_API_VERSION) do |config|
  
  config.vm.box = "ubuntu/xenial64"
  config.vm.network "private_network", ip: "192.168.33.56"
  config.vm.synced_folder "./", "/var/www/html/", id: "vagrant-root", :group=>'www-data', :mount_options=>['dmode=775,fmode=775']
  config.vm.provision :shell, path: "bootstrap.sh"
  config.vm.provider :virtualbox do |vb|
          vb.name = "clearlivingclub"
  end
  #config.vm.network "forwarded_port", guest: 5432, host: 5434

end
