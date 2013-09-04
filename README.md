phpunit-test-db
===============

Library to clone a database for testing purposes

Test\_Db is intended to be used for unit testing with PHPUnit in applications
based on ZendFramework 1.

System Requirements
-------------------
* PHP 5.3 or higher
* PHPUnit 3.5

Installation
------------

1. Copy library/Test\_Db.php to the library of your application.
2. Tell your unittests to make use of the library by setting up in the setUp and tearDown methods. You will find an example in: 

Example
-------

Base on the sample "ZendFramework Quick Start application" the test file
 tests/application/controllers/GuestbookControllerTest.php 
makes use of Test\_Db
