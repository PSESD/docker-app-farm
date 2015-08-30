#!/usr/bin/env bash

STORAGE_DIR="/var/transfer/$1"
echo $STORAGE_DIR
if [ ! -d $STORAGE_DIR ]; then
	mkdir -p $STORAGE_DIR
fi
chmod -R 0755 $STORAGE_DIR
chown -R www-data $STORAGE_DIR

if [ -d $STORAGE_DIR ]; then
	echo "----TRANSFER_PREPARE_SUCCESS----"
fi