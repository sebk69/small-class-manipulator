FROM php:8.1-cli

# install composer
RUN apt-get update && \
    apt-get install -y git zip
RUN php -r "copy('https://getcomposer.org/installer', 'composer-setup.php');"
RUN php composer-setup.php --install-dir=/usr/bin --filename=composer
RUN chmod 755 /usr/bin/composer

# system setup
WORKDIR /usr/lib/small-class-manipulator

# run tests
COPY . /usr/lib/small-class-manipulator

RUN COMPOSER_ALLOW_SUPERUSER=1 composer update
RUN if [ '$BUILD' == '1' ]; then rm -r tests/data/Empty/* && ./vendor/bin/phpunit --testdox tests; fi

ENTRYPOINT bash -c 'if [ '$BUILD' == '0' ]; then sleep infinity; fi'