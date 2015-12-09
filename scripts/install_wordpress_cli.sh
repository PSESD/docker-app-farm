#!/usr/bin/env bash

echo "Setting up..."
if [ ! -d "/var/www/client" ]; then
	mkdir /var/www/client
fi

if [ ! -d "/var/www/web" ]; then
	mkdir /var/www/web
fi

chown www-data -R /var/www
cd /var/www/client

if [ -e "/var/www/client/wp-cli.phar" ]; then
	echo "Deleting old wp-cli.phar..."
	rm -f /var/www/client/wp-cli.phar
fi
if [ -e "/var/www/client/wp" ]; then
	echo "Deleting old wp..."
	rm -f /var/www/client/wp
fi

sleep 2

echo "Downloading wp-cli.phar..."
wget -q https://raw.githubusercontent.com/wp-cli/builds/gh-pages/phar/wp-cli.phar
if [ -e "/var/www/client/wp-cli.phar" ]; then
	chmod +x wp-cli.phar
fi

echo "Downloading wp (helper)..."
wget -q https://raw.githubusercontent.com/canis-io/docker-app-farm/master/scripts/wp

if [ -e "/var/www/client/wp" ]; then
	chmod +x wp
fi

cd /var/www
echo "Downloading wp-cli.yml..."
if [ -e "/var/www/wp-cli.yml" ]; then
	rm /var/www/wp-cli.yml
fi

wget -q https://raw.githubusercontent.com/canis-io/docker-app-farm/master/scripts/wp-cli.yml

if [ -e "/var/www/wp-cli.yml" ]; then	
	if [ -e "/var/www/client/wp" ]; then	
		if [ -e "/var/www/client/wp-cli.phar" ]; then
			echo "----WORDPRESS_CLI_INSTALL_SUCCESS----"
		fi
	fi
fi
