FROM php:7.4-cli

# Update system
RUN apt-get update && rm /etc/apt/preferences.d/no-debian-php && apt-get install --yes git libzip-dev zip libxml2-dev libevent-dev libssl-dev libgmp-dev libmcrypt-dev libsodium-dev

# Install composer
ENV COMPOSER_HOME "/home/www-data/.composer"

RUN mkdir -p $COMPOSER_HOME && chmod -R 777 $COMPOSER_HOME && curl -sS https://getcomposer.org/installer | php \
  && chmod +x composer.phar && mv composer.phar /usr/local/bin/composer

RUN docker-php-ext-install bcmath json pcntl sockets && \
    pecl install event && \
    docker-php-ext-enable event

# Copy launch scripts
COPY launch.sh /usr/local/bin/
ENTRYPOINT ["sh", "/usr/local/bin/launch.sh"]
