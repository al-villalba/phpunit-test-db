phpunit-test-db
===============

Library to clone a database for testing purposes

[Test\_Db](https://github.com/al-villalba/phpunit-test-db/blob/master/library/Test/Db.php)
is intended to be used for unit testing with PHPUnit in applications based on
ZendFramework 1.

System Requirements
-------------------
* PHP 5.3 or higher
* PHPUnit 3.5 or higher
* ZendFramework-1.x

Installation
------------

1. Copy
[library/Test\_Db.php](https://github.com/al-villalba/phpunit-test-db/blob/master/library/Test/Db.php)
to the library of your application.

2. Tell your unittests to make use of the library by setting up in the setUp
and tearDown methods. You will find an example in: 

Example
-------

Base on the sample "ZendFramework Quick Start application" the test file
[tests/application/controllers/GuestbookControllerTest.php]
(https://github.com/al-villalba/phpunit-test-db/blob/master/tests/application/controllers/GuestbookControllerTest.php) 
makes use of [library/Test\_Db.php](https://github.com/al-villalba/phpunit-test-db/blob/master/library/Test/Db.php)

