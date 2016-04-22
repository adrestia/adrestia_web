# adrestia
College Confessions Web API and Frontend

## About
This is the github repo for the web api as well as the frontent.

Technologies used:   
Symfony 3  
MySQL 5.6  
Doctrine 2  
Doctrine Extensions  
OAuth Server Bundle  
Composer  
Assetic  
Twig  

## Installation
#### Clone the repository. I recommend with ssh keys.
```
git clone git@github.com:adrestia/adrestia_web.git
cd adrestia_web
```

#### Modify permissions with the following commands
```
rm -rf var/cache/* var/logs/* var/sessions/*

HTTPDUSER=`ps axo user,comm | grep -E '[a]pache|[h]ttpd|[_]www|[w]ww-data|[n]ginx' | grep -v root | head -1 | cut -d\  -f1`
sudo chmod -R +a "$HTTPDUSER allow delete,write,append,file_inherit,directory_inherit" var
sudo chmod -R +a "`whoami` allow delete,write,append,file_inherit,directory_inherit" var
```
This is so that the webserver can write to the cache file properly.  
If these instructions don't work, check [here](http://symfony.com/doc/current/book/installation.html#checking-symfony-application-configuration-and-setup)  

#### Create app/config/parameters.yml
Put this inside of it:  
```
parameters:
    database_host: 127.0.0.1
    database_port: null
    database_name: adrestia
    database_user: adrestia
    database_password: password
    mailer_transport: smtp
    mailer_host: 127.0.0.1
    mailer_user: null
    mailer_password: null
    secret: ThisIsNotASecretChangeIt
```

#### Check out the dev branch
`git fetch origin dev:dev`
`git checkout dev`

#### Configure MySQL
If you don't have mysql installed, install it  
`sudo apt-get install mysql-server`

Log into MySQL to configure it  
`mysql -uroot`

Create the database named adrestia.   
`CREATE DATABASE adrestia;`  
Configure the increment variables. Similar to ClearDB.  
`SET GLOBAL auto_increment_increment = 10;`  
`SET GLOBAL auto_increment_offset = 2;`  
Create a new user and grant permissions. This is so we don't have to use root.  
`CREATE USER 'adrestia'@'localhost' IDENTIFIED BY 'password';`  
`GRANT ALL PRIVILEGES ON * . * TO 'adrestia'@'localhost';`  
After done, you have to flush MySQL so it can reload the configurations.  
`FLUSH PRIVILEGES;`  

#### Pull the vendor information with composer
If you don't have composer, install it [here](https://getcomposer.org/download/).  
Run `composer update` or `composer install`.
This will download all of the dependencies outlined in the composer.json.  
*Composer* is a dependency management tool for PHP and is closely integrated into Symfony. USE IT.  
You can find more information about it by reading its documentation [here](https://getcomposer.org/doc/00-intro.md).  

#### Configure Doctrine to update the database
`php bin/console doctrine:schema:update --force`  

If you want to see the SQL that is being created to see the tables, run  
`php bin/console doctrine:schema:update --dump-sql`

#### Start the web server
Run the command
`php bin/console server:run -vvv`

If you want the server to run in the background
`php bin/console server:start`

If you have set everything up right, you can access your copy of the server at `http://localhost:8000`.

#### Clear the cache after making changes
If you make any changes to the HTML, CSS, or JavaScript you'll have to clear the asset cache.  
`php bin/console cache:clear --env=dev`
