# ![Laravel Example App](xTenant_Logo.png)
xTenant handles everything for you to make your Laravel app multi-tenant ready with ease.

------

## TABLE OF CONTENTS
- Features
- Installation
- Getting Started
- Usage
- Road Map
- FAQ
- Support
- Feedback
- Contribution

## FEATURES
- Easy install
- Multiple databases
- Support: MySQL and SQLite
- Subdomain tenants
- Create/Edit/Delete
- Backup & restore tenants' DBs & files

## INSTALLATION
You can install this package via Composer by running this command in your terminal in the root of your project:

`composer require fbnkcmaster/xtenant`

## GETTING STARTED (with an example)

To get started, I have prepared a demo app to test this package.

Go ahead and install the demo-app you can find here:

  <a href="https://github.com/FBNKCMaster/demo-app" target="_blank"> https://github.com/FBNKCMaster/demo-app</a>

If everything is ok, switch to the repo folder where you have installed demo-app

    cd demo-app

Install this package using composer

    composer require fbnkcmaster/xtenant

## USAGE

Here are the steps and commands you will need to use this package:

1/ First, setup the package

    php artisan xtenant:setup

  You will be asked to enter the "SuperAdmin" subdomain and if you want to allow "www" for subdomains.

2/ Create your first tenant

    php artisan xtenant:new

You will have to choose a subdomain, a name and a description for this tenant.
If the subdomain already exists, you will be asked to edit or override it.
The you will be asked if you want to run migrations, seeds and create directory for this tenant.

3/ That's all. If everything is ok you will be able to access your tenant at:
    
    http://[tenant_subdomain].demo-app.test


## ROAD MAP
- SuperAdmin web interface to manage tenants
- PostgreSQL support
- Handeling Queues
- Handeling Events

## FAQ
- Is it free?
  > Yes!
- Can I contribute?
  > Yes!
- How to support?
  > Everything is welcome :)

## FEEDBACK
Feedbacks are welcome!
Feel free to open issues or direct contact me via twitter (@FBNKCMaster)

## CONTRIBUTION
All contributions are welcome!