#!/usr/bin/env bash

# some other niceties could be found here:
# https://www.snip2code.com/Snippet/16602/Vagrant-provision-script-for-php--Apache

# update / upgrade
sudo apt update
sudo apt -y upgrade

# install MySQL
echo "mysql-server mysql-server/root_password password 1q2w3e4r5t6y" | debconf-set-selections
echo "mysql-server mysql-server/root_password_again password 1q2w3e4r5t6y" | debconf-set-selections
sudo apt -y install mysql-server

mysql -u root -p1q2w3e4r5t6y -e "CREATE DATABASE clean;";

# install apache2 and other tools
sudo apt -y install apache2 htop mc unzip git curl

sudo apt -y install php libapache2-mod-php php-mcrypt php-mysql php-xml php-curl \
    php-mbstring php-curl memcached php-memcache libapache2-mod-xsendfile

sudo service apache2 restart

sudo apt autoremove

sudo apt clean

# setup hosts file
VHOST=$(cat <<EOF
<VirtualHost *:80>
    DocumentRoot "/var/www/html/"
    <Directory "/var/www/">
        AllowOverride All
        Require all granted
    </Directory>
</VirtualHost>
EOF
)
echo "${VHOST}" > /etc/apache2/sites-available/000-default.conf

# enable mod_rewrite
sudo a2enmod rewrite

# create local config file
cp /var/www/html/app/config/_localConfig.php.default /var/www/html/app/config/_localConfig.php
#
# install Composer
sudo curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin \
    && ln -s /usr/local/bin/composer.phar /usr/local/bin/composer \
    && composer global require "fxp/composer-asset-plugin:~1.2.0"

# install Composer plugin
cd /var/www/html/app/
#    && composer global require "fxp/composer-asset-plugin:~1.2.0" \
composer install

# migrate DB
php yii migrate/up 0 --interactive=0

# restart apache
sudo service apache2 restart
