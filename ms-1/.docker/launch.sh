#!/bin/bash

setUpHostName() {
  echo "Check if should add hostname to Apache Configuration file"
  shouldAddHostName=$(grep -c ServerName /etc/apache2/sites-available/000-default.conf)

  if [ "$shouldAddHostName" -eq "0" ];
  then
    echo "Updated ServerName to " $HOST_NAME
    echo "ServerName "$HOST_NAME"\n" >> /etc/apache2/sites-available/000-default.conf
  fi
}

# Define host name from env if not present
setUpHostName

composer install --working-dir=/srv/app

# Restart apache with configuration file
apachectl -D FOREGROUND
