Requirements
============

 - Apache ^2.4.25
 - Linux Debian/Ubuntu
 - PHP ^7.4.14
 - MariaDB ^10.1.48
 - composer
 - yarn

# Installation Silk
**Fetch git repository**

    git clone https://github.com/TonyHaikara/silk-platform.git silk

**Move into Silk installation folder**

    cd silk

**Create and edit file .env.local**

    APP_ENV=production
    DATABASE_URL=mysql://[USER_NAME]:[PASSWORD]@[MYSQL_SERVER_IP]:3306/[DATABASE_NAME]?serverVersion=5.7

**Run composer**
composer install

**Set folder rights**

    chmod -R 777 var/log
    chmod -R 777 var/cache

**Create and migrate database**

    php bin/console doctrine:database:create
    mysql -u [USER_NAME] -p[PASSWORD] [DATABASE_NAME] < silk.sql
    php bin/console doctrine:migrations:migrate

**Load fixtures data**
php bin/console doctrine:fixtures:load --append

**Install packages and generate assets**

    yarn install
    yarn encore production
