#!/usr/bin/env bash

echo "Setting up..."
if [ ! -d "/var/www/client" ]; then
	mkdir /var/www/client
fi

if [ ! -d "/var/www/web" ]; then
	mkdir /var/www/web
fi

chown www-data -R /var/www


echo "Downloading wp-cli.phar..."
if [ -d "/var/www/client/wp-cli.phar" ]; then
	rm /var/www/client/wp-cli.phar
fi
wget -q https://raw.githubusercontent.com/wp-cli/builds/gh-pages/phar/wp-cli.phar
if [ -e "/var/www/client/wp-cli.phar" ]; then
	chmod +x wp-cli.phar
fi

echo "Downloading wp (helper)..."
if [ -e "/var/www/client/wp" ]; then
	rm /var/www/client/wp
fi
wget -q https://raw.githubusercontent.com/canis-io/docker-app-farm/master/scripts/wp

if [ -e "/var/www/client/wp" ]; then
	chmod +x wp
fi


echo "Downloading wp-cli.yml..."
if [ -e "/var/www/client/wp-cli.yml" ]; then
	rm /var/www/client/wp-cli.yml
fi

wget -q https://raw.githubusercontent.com/canis-io/docker-app-farm/master/scripts/wp-cli.yml

if [ -e "/var/www/client/wp-cli.yml" ]; then	
	if [ -e "/var/www/client/wp" ]; then	
		if [ -e "/var/www/client/wp-cli.phar" ]; then
			echo "\n\n\n----WORDPRESS_CLI_INSTALL_SUCCESS----\n\n\n"
		fi
	fi
fi
