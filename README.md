# adrestia
College Confessions Web API and Frontend

## About
This is the github repo for the web api as well as the frontent.

Technologies used: 
Symfony 3
MySQL 5.6
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
$ rm -rf var/cache/* var/logs/* var/sessions/*

$ HTTPDUSER=`ps axo user,comm | grep -E '[a]pache|[h]ttpd|[_]www|[w]ww-data|[n]ginx' | grep -v root | head -1 | cut -d\  -f1`
$ sudo chmod -R +a "$HTTPDUSER allow delete,write,append,file_inherit,directory_inherit" var
$ sudo chmod -R +a "`whoami` allow delete,write,append,file_inherit,directory_inherit" var
```
This is so that the webserver can write to the cache file properly. \n
If these instructions don't work, check [here](http://symfony.com/doc/current/book/installation.html#checking-symfony-application-configuration-and-setup)\n

#### Download the parameters file from Kyle
This should be pretty self explanatory. 
If you need access to the database, please ask.
You can reach me by email, `kyleminshall@gmail.com`

#### Pull the vendor information with composer
Run `composer update` or `composer install`.
This will download all of the dependencies outlined in the composer.json.
*Composer* is a dependency management tool for PHP and is closely integrated into Symfony. USE IT.
You can find more information about it by reading its documentation [here](https://getcomposer.org/doc/00-intro.md).

#### Start the web server
Run the command
`php bin/console server:start`

If you have set everything up right, you can access your copy of the server at `http://localhost:8000`.

#### Clear the cache after making changes
`php bin/console cache:clear --env=dev`