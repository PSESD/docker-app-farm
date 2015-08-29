#!/usr/bin/env bash
cd /tmp
mkdir /var/www/web
wget -q https://wordpress.org/latest.tar.gz
if [ -e "latest.tar.gz" ]; then
	tar xzf latest.tar.gz -C /var/www/web --strip-components=1
	rm latest.tar.gz
	cd /var/www
	chown www-data -R *
fi

if [ -e "/var/www/web/wp-load.php" ]; then
	echo "\n\n\n----INSTALL_SUCCESS----\n\n\n";
fi