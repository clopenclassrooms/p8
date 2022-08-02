## Installation

Installation prerequisites :
* docker installed

Commande line :
```Bash
git clone https://github.com/clopenclassrooms/p8.git
```

## Start the Website
Commande line :
```Bash
#Launch web and sql server
cd p8
docker-compose up

#Access to symfony server's commande line
chmod +x ./sbash.sh & ./sbash
cd p8

#Install prerequisites
composer install

#Inject DATA
chmod +x ./sbash.sh & ./sbash
cd p8
php bin/console doctrine:fixtures:load
```

## access to Website

Website :
* http://localhost

Admin account :
* admin/admin

User account :
* user1/user1
* user2/user2

## access to PhpMyAdmin
PhpMyAdmin :
* http://localhost:8001

Server : mariadb
User : bobby
Password : tables