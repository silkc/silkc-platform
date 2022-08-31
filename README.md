Requirements
============

 - Apache ^2.4
 - Linux Debian/Ubuntu
 - PHP ^7.4.14 (PHP 8.* not supported yet)
 - MariaDB ^10.1
 - composer
 - yarn

# Silkc platform installation
**Fetch git repository**

    git clone https://github.com/silkc/silkc-platform.git silkc

**Move into Silk installation folder**

    cd silkc

**Create and edit file .env.local**

    APP_ENV=production
    DATABASE_URL=mysql://[USER_NAME]:[PASSWORD]@[MYSQL_SERVER_IP]:3306/[DATABASE_NAME]?serverVersion=5.7

**Run composer**

    composer install

**Set folder permissions**

    chmod -R 777 var/log
    chmod -R 777 var/cache

**Create and migrate database**

    php bin/console doctrine:database:create
    php bin/console doctrine:schema:update --force
    mysql -u [USER_NAME] -p[PASSWORD] [DATABASE_NAME] < data.sql

**Install packages and generate assets**

    yarn install
    yarn encore production

**Load fixtures data**

Loading fixtures will create 4 user accounts:
- Admin role: admin/admin
- Final user role: user/user
- Institution role: institution/institution
- Recruiter role: recruiter/recruiter

These accounts *must* be secured later on by changing their password or removing them. The admin account must not be removed.

    php bin/console doctrine:fixtures:load --group=AppFixtures --append --env dev

Import institution profiles from src/DataFixtures/JSON/xxx.json file

    php bin/console doctrine:fixtures:load --group=InstitutionImportFixtures --append --env dev

Import training from src/DataFixtures/JSON/xxx.json file.
Training data like startAt and endAt is date-checked in src/Entity/Training.php through `@Assert` clauses.
To avoid validation issues while importing, we recommend removing the following lines in Training.php and 
launching `composer install` to register those changes, before importing training and users (which can also
contain training declarations).
```
# In $startAt declaration block (line ~255), remove
     * @Assert\GreaterThan("today")
# In $endAt declaration block (line ~263), remove
     * @Assert\Expression("value == null or value > this.startAt", message="training_create_ent_at_have_to_be_greather_than_start_at")
```
You can then issue the import command:

    php bin/console doctrine:fixtures:load --group=TrainingImportFixtures --append --env dev

To fetch or generate latitude and longitude for trainings with only location, run this url :

    https://mydomain.ext/api/cron/fetch_lat_and_long

Import user profiles from src/DataFixtures/JSON/xxx.json file

    php bin/console doctrine:fixtures:load --group=UserImportFixtures --append --env dev

**Development environment**

For development environments, make sure the following is present in the .env.local file:

    APP_ENV=dev
    APP_DEBUG=true

**Start using SILKC**

Load the application URL and login as admin/admin.
If any error appears, make sure you apply the right permissions for var/log and var/cache as described above.
