#!/usr/bin/env bash

sudo apt-get update
sudo apt-get install -y apache2
if ! [ -L /var/www ]; then
  rm -rf /var/www
  ln -fs /vagrant /var/www
fi

sudo apt-get install -y php5 libapache2-mod-php5 php5-mcrypt

#gd image library
sudo apt-get install -y php5-gd

#imagick
sudo apt-get install -y imagemagick
sudo apt-get install -y php5-imagick

sudo apachectl restart