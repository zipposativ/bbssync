#!/usr/bin/bash

#root check
if [ "$(id -u)" -ne 0 ]; then
  echo "Dieses Skript muss als root ausgeführt werden."
  exit 1
fi

#install git
apt install -y git > /dev/null 2>&1

#stop webserver
systemctl stop nginx

#create Backup
mkdir -p /var/bbssync/backup
cp -r /etc/bbssync/www/ /var/bbssync/backup

#get new version
rm -r /etc/bbssync/
rm -r /tmp/bbssync
mkdir -p /tmp/bbssync
git clone https://github.com/zipposativ/bbssync.git /tmp/bbssync
#create base dir
mkdir -p /etc/bbssync
cp -r /tmp/bbssync/www/ /etc/bbssync

#restore config
cp -r /var/bbssync/backup/www/bbssync/config.php /etc/bbssync/www/bbssync/config.php
cp -r /var/bbssync/backup/www/bbssync/userdata /etc/bbssync/www/bbssync/
#change ownership
chown -R www-data:www-data /etc/bbssync

#start web server
systemctl start nginx
