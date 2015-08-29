cd /tmp
mkdir /var/www/web
wget https://wordpress.org/latest.tar.gz
tar xvzf latest.tar.gz -C /var/www/web --strip-components=1
rm latest.tar.gz
cd /var/www
chown www-data -R *