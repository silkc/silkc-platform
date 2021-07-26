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
    php bin/console doctrine:schema:update --force
    mysql -u [USER_NAME] -p[PASSWORD] [DATABASE_NAME] < data.sql

**Load fixtures data**

Loading fixtures will create 3 user accounts:
- Admin role: admin/admin
- Final user role: user/user
- Institution role: institution/institution

These accounts *must* be secured later on by changing their password or removing them. The admin account must not be removed.

    php bin/console doctrine:fixtures:load --group=AppFixtures --append --env dev

Import institution profiles from src/DataFixtures/JSON/xxx.json file

    php bin/console doctrine:fixtures:load --group=InstitutionImportFixtures --append --env dev

Import training from src/DataFixtures/JSON/xxx.json file

    php bin/console doctrine:fixtures:load --group=TrainingImportFixtures --append --env dev

**Install packages and generate assets**

    yarn install
    yarn encore production
