## DISAA - Digital identity for an academic account system integrating SSO technologies and API RESTfull

Web project for the creation of academic accounts.

## The elevator pitch
- For students

- who need to have a account 

- the Digital identity for an academic account system integrating SSO technologies and API RESTfull

- is a management of digital identity

- that manage and administer academic accounts

- Unlike of another software that they can make the same

- our project is going to make with a custom development with better communication between academic and students

# Project installation

## System requirements

1) You must have PHP version greater than or equal to 8.0.7

2) You must have composer installed on your computer: https://getcomposer.org/

3) You must have git installed, in my case the windows version: https://gitforwindows.org/

## Instructions

- **To clone the project use the following command** git clone https://github.com/fatandazdba/DISAA.git/

- **run** composer install

- copy the file **.env.example** and paste it with the name: **.env**. 

- Database configuration in the ".env" file:
```
    DB_CONNECTION=mysql
    DB_HOST=127.0.0.1
    DB_PORT=3306
    DB_DATABASE=DISAA
    DB_USERNAME=root
    DB_PASSWORD=
```

- **Start the project** php -S localhost:8000 -t public 

- **Now you can see the home page**


