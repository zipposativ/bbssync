#!/usr/bin/bash

if [ "$(id -u)" -ne 0 ]; then
  echo "Dieses Skript muss als root ausgeführt werden."
  exit 1
fi

#Needed for PHP 8.4
echo "PHP 8.4 install"
apt install -y lsb-release apt-transport-https ca-certificates wget curl git > /dev/null 2>&1
wget -O /etc/apt/trusted.gpg.d/php.gpg https://packages.sury.org/php/apt.gpg > /dev/null 2>&1
echo "deb https://packages.sury.org/php/ $(lsb_release -sc) main" | tee /etc/apt/sources.list.d/php.list > /dev/null 2>&1
apt update -y > /dev/null 2>&1
apt install -y php8.4 php8.4-fpm php8.4-ldap php8.4-curl > /dev/null 2>&1

#Load packages
echo "Disable Apache2 & install nginx, samba"
systemctl stop apache2 > /dev/null 2>&1
apt install -y nginx samba > /dev/null 2>&1

#Create dir
echo "Create Base Directory"
mkdir -p /etc/bbssync/www > /dev/null 2>&1
#move file
echo "Create Nginx Config"
cp -r ./www /etc/bbssync/ > /dev/null 2>&1
cp ./nginx/bbssync.conf /etc/nginx/sites-available/ > /dev/null 2>&1
ln -s /etc/nginx/sites-available/bbssync.conf /etc/nginx/sites-enabled/ > /dev/null 2>&1
rm -r /etc/nginx/sites-enabled/default > /dev/null 2>&1
systemctl restart nginx > /dev/null 2>&1

#move bbssync server file
cp -r ./www/ /etc/bbssync/www/
chown www-data:www-data /etc/bbssync/www/

#adduser
echo "Create SMB User"
adduser \
   --system \
   --shell /bin/bash \
   --group www-data \
   --disabled-password \
   --home /home/bbssync \
   bbssync
smbpasswd -a bbssync

#edit smb
echo "Create Samba Config"
rm -r /etc/samba/smb.conf
cp ./smb/smb.conf /etc/samba/
systemctl restart smbd

echo "Finished"
echo "You cann Access via:"
echo "https://$(hostname -I | awk '{print $1}')/bbsysync"
echo "Or via:"
echo "https://$(hostname)/bbsysync"
