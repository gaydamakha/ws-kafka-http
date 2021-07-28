FROM php:7.4-apache

# Variables
ARG vhostFile=/etc/apache2/sites-available/000-default.conf
ARG phpIniFile=/usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini
ARG DOCUMENT_ROOT=/srv/app

# Update system
RUN apt-get update && rm /etc/apt/preferences.d/no-debian-php && apt-get install --yes git libzip-dev zip libxml2-dev libevent-dev libssl-dev libgmp-dev libmcrypt-dev libsodium-dev

# Install composer
ENV COMPOSER_HOME "/home/www-data/.composer"

RUN mkdir -p $COMPOSER_HOME && chmod -R 777 $COMPOSER_HOME && curl -sS https://getcomposer.org/installer | php \
  && chmod +x composer.phar && mv composer.phar /usr/local/bin/composer

RUN mkdir $DOCUMENT_ROOT

RUN docker-php-ext-install pdo pdo_mysql bcmath json pcntl && a2enmod rewrite

RUN docker-php-ext-install sockets && \
    pecl install event && \
    docker-php-ext-enable event

RUN echo "<VirtualHost *:80>\n" > $vhostFile \
         "    DocumentRoot "$DOCUMENT_ROOT"/public\n" >> $vhostFile \
         "    HttpProtocolOptions unsafe\n" >> $vhostFile \
         "    <Directory \""$DOCUMENT_ROOT"\">\n" >> $vhostFile \
         "        AllowOverride all\n" >> $vhostFile \
         "        Require all granted\n" >> $vhostFile \
         "    </Directory>\n" >> $vhostFile \
         "</VirtualHost>\n\n" >> $vhostFile

# Copy launch scripts
COPY launch.sh /usr/local/bin/
ENTRYPOINT ["sh", "/usr/local/bin/launch.sh"]
