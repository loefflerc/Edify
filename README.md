Edify
=====

```
ed·i·fy  
/ˈedəˌfī/

Verb
Instruct or improve (someone) morally or intellectually.

Synonyms
educate
```

Introduction
-----

Edify is an educational project for the PHP Programmers community. The idea is learning by doing. This project is ment to be educational only, and does not have any intentions of being a competitor to any other existing framework or library.

Description
-----

The goal for Edify is to build a component library based on standalone packages that has no dependencies outside the package itself. The idea is to remove the rules and restrictions that all frameworks and most libraries today are forcing the developer to follow. The developer can grab only the packages that is needed for the current project and ignore everything else.

Standards & Requirements
------

The CL will follow PSR-0, PSR-1 and PSR-2, but will not include PSR-3 since this imposes unnecessary dependencies.

All packages MUST be written with proper corresponding unit tests to provide proof that the package is robust.

Every single class MUST follow the [single responsibility principal](http://www.oodesign.com/single-responsibility-principle.html)

If a package contains a base accessor object, this object MUST follow the factory pattern and should be written as dynamically as possible.

For example, when working with the database package, even though we have a factory object that will call the requested driver, the call for each driver MUST NOT be hardcoded into the factory object. This way we are not adding any unnecessary dependencies. The factory will not know about the drivers, and vice versa.

This is how it will work:  
The name of the class and file are identical, so instead of hardcoding the objects into the factory we can use realpath() to check if a file with the requested driver name exists.

Here’s a sample code of how I have solved this previously

```php
$driverPath = dirname(__FILE__) . "/Drivers/{$config['driver']}.php";
if (false === ($driverRealPath = realpath($driverPath))) {
    throw new InvalidArgumentException("Unsupported driver");
}
$driverName = basename($driverRealPath, ‘.php’);
$driver = __NAMESPACE__ . “\Drivers\{$driverName}”;
$driver = new $driver($config);
```

Container packages MUST NOT depend on any of its sub package.

All dependencies MUST be included using the //use// operated right below the namespace.

All classes, methods, constants and properties MUST be well documented. This includes DocBlock tags for all parameters, returns, use operators, throwing exceptions, and so on.

Packages
------

This is listed of packages that should be present in the library. Please see the "Package Details" for more information

  * Database
  * Html
  * Email
  * Cli
  * Files
  * Utils
  * Security
  * WebServices

Package Details
------

**Database**

A layer that easily helps you connect to a supported driver. It should have a singleton base class (e.g. Database) which follows the [factory method pattern.](http://www.oodesign.com/factory-method-pattern.html)

The call for a driver MUST NOT be hardcoded into the factory object. This way we are not adding any unnecessary dependencies. The factory will not know about the drivers, and vice versa.

For example:  
```php
Database::connect(array $config)
```

Some suggestions for classes is:

  * Database - This will be the factory object
  * Connection - The actual connection class
  * Table - Handles anything related to a database table
  * Schema - Handles anything related to the database schema
  * QueryBuilder - Help create good queries
  * Mptt - Class to handle the [Modified Preordered Tree Traversal](http://www.sitepoint.com/hierarchical-data-database-2/) design
  * Drivers (e.g: MySQL and SQLite)

**Html**

This is a container package that will contain sub package related to HTML generation. An example of a sub package would be Form.

**Email**

Send email using either Smtp, Mail or Sendmail. This uses the same approach as the Database package.

**Cli**

Command line interface objects. These objects should never be used for anything else than to be called directly from the cli.

**Files**

This is another container package. Here we will have a class in the Files package root that contains common actions like; rename, move, copy and delete. There will also be a separate class for uploading files. Some sub packages will be Images, PDF, etc.

**Utils**

The Utils package will contain helper classes like; validation, string manipulation, date and time, etc.

**Security**

This package will contain Acl, 

**WebServices**

Helpero bjects against web service APIs such as; Facebook, Foursquare, Google, Tumblr, Twitter and Yahoo.

**Payment**

This is maybe something that should be considered when the project has been running for a bit. But I don’t see it as impossible to include objects that helps in communicating with payment gateways and APIs like PayPal and Stripe