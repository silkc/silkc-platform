Requirements
============

 - Apache ^2.4.25
 - Debian 9.13
 - PHP ^7.4.14
 - MariaDB ^10.1.48
# Installation Silk
**Fetch git repository**

    git clone https://github.com/TonyHaikara/silk-platform.git silk

**Move into Silk installation folder**

    cd silk

**Create and edit file .env.local**

    APP_ENV=production
    DATABASE_URL=mysql://[USER_NAME]:[PASSWORD]@[MYSQL_SERVER_IP]:3306/[DABASE_NAME]?serverVersion=5.7
**Run composer**
composer install

**Set folder rights**

    chmod -R 777 var/log
    chmod -R 777 var/cache

**Create and migrate database**

    php bin/console doctrine:database:create
    php bin/console doctrine:migrations:migrate

**Install packages and generate assets**

    yarn install
    yarn encore production
