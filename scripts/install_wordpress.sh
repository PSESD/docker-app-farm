#!/bin/bash
cd /tmp
wget https://wordpress.org/latest.tar.gz
tar xvzf latest.tar.gz -C /var/www --strip-components=1
cd /var/www