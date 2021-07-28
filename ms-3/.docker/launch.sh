#!/bin/bash

composer install --working-dir=/srv/app

php /srv/app/src/listener.php
