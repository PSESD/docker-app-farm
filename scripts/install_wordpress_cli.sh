#!/usr/bin/env bash
if [ ! -d "/var/www/client" ]; then
	mkdir /var/www/client
fi
cd /var/www/client
if [ ! -d "/var/www/client/wp-cli.phar" ]; then
	rm /var/www/client/wp-cli.phar
fi

wget -q https://raw.githubusercontent.com/wp-cli/builds/gh-pages/phar/wp-cli.phar
if [ -e "/var/www/client/wp-cli.phar" ]; then
	if [ ! -d "/var/www/client/wp" ]; then
		rm /var/www/client/wp
	fi
fi


if [ -e "/var/www/client/wp" ]; then
	echo "\n\n\n----WORDPRESS_CLI_INSTALL_SUCCESS----\n\n\n"
fi