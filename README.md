PHP CRUD PDO-V2
=============
PHP CRUD Lib 2.0 - 9 April 2015

By Mehdi Rochdi

PHP Class/MySQL use, create, all, update and delete and other functions. It uses PDO driver
it's capable to interacting with your mysql database, with easy methods inspire since framworks (cakePHP).
you can integrate into you OOP architecture

### Installation
-----------------

Clone the repository
	
	git clone https://github.com/mehdirochdi/php-crud-V2.git

Download composer:

	curl -sS https://getcomposer.org/installer | php

Install vendors:

    php composer.phar install

### How Using The Class
-----------------
You will need to change some variable in config.php, for your own Database local and a distance

```php
"db_host" => "localhost", // change as required
"db_user" => "username",  // change as required
"db_pass" => "password",  // change as required
"db_name" => "database_name", // change as required

```
Test Mysql

Start by creating test table in your Database

```php
CREATE TABLE IF NOT EXISTS `authors` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `emails` varchar(255) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

INSERT INTO posts VALUES('', 'Name 1', 'name 1@email.com');
INSERT INTO posts VALUES('', 'Name 2', 'name 2@email.com');
INSERT INTO posts VALUES('', 'Name 3', 'name 3@email.com');
INSERT INTO posts VALUES('', 'Name', 'name 4@email.com');

```

### Examples
-----------------
###### Insert exemple

```php
<?php
require 'vendor/autoload.php'; // Autoload
use oop\Core\Table\Table;

$db = new Table();
$db->table = 'authors'; // Table name

$response = $db->create([
	
	'name' => 'Name 5',
	'email' => 'name 5@email.com',
]);

echo 'ID : '.$db->lastInsertId(); // Last insert ID
var_dump($response);
?>
```
##### Select Data with a function read(bool $tinyint, int $numberOfPage, array $order)

```php
<?php
require 'vendor/autoload.php'; // Autoload
use oop\Core\Table\Table;

$db = new Table();
$db->table = 'authors'; // Table name
$response = $db->all();
var_dump($response);

?>
```
######Use function all() with only option (order)

```php
<?php
require 'vendor/autoload.php'; // Autoload
use oop\Core\Table\Table;

$db = new Table();
$db->table = 'authors'; // Table name
$response = $db->all([
	'order' => ['id' => 'DESC']
]);
var_dump($response);

?>
```
######Use function all() for Pagination exemple

```php
<?php
require 'vendor/autoload.php'; // Autoload
use oop\Core\Table\Table;

$db = new Table();
$db->table = 'authors'; // Table name
$response = $db->read(true,4, [
		'order' => ['id' => 'DESC']
]);
echo 'The count of row for each page is : '.$db->rowCount().'<br/>';
echo 'The total count of rows is : '.$db->countStatement;
var_dump($response);

// Display Pagination
for($i=1; $i<=$db->_paginate_number; $i++){

	if($i == $db->_paginate_currentPage){

		echo ' / '.$i;

	}else{

		echo ' / <a href="index.php?page='.$i.'">'.$i.'</a>';

	}
}
?>
```
######For more possibilities you can use function find() or findById(int $id, string $fetch_mode)
######you can choice your favorite fetch mode ('num', 'both', 'assoc', 'obj')

```php
<?php
require 'vendor/autoload.php'; // Autoload
use oop\Core\Table\Table;

$db = new Table();
$db->table = 'authors'; // Table name
$response->$db->findById(1, 'obj'); // num || both || assoc || obj 
var_dump($response);

?>
```
######for advanced requirements, use 
######find(string $genre, array $params, array $attribute, string fetch_mode, int $numberOfPage)

```php
<?php
require 'vendor/autoload.php'; // Autoload
use oop\Core\Table\Table;

$db = new Table();
$db->table = 'authors'; // Table name
$response->$db->find('all', [
'fields'     => ['name', 'emails'],
'conditions' => ['id' => '?'],
'order' => ['id' => 'DESC']
], ['2']);
var_dump($response);

?>
```
##### Joins exemple with function find()

######start by Another table in your Database

```php
CREATE TABLE IF NOT EXISTS `posts` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `author_id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `description` text NOT NULL,
  `is_actived` tinyint(1) NOT NULL DEFAULT '1',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

INSERT INTO posts VALUES('', 1, 'Post name 1', 'my description for post 1');
INSERT INTO posts VALUES('', 1, 'Post name 1', 'my description for post 2');
INSERT INTO posts VALUES('', 1, 'Post name 1', 'my description for post 3');
INSERT INTO posts VALUES('', 2, 'Post name 1', 'my description for post 4');
INSERT INTO posts VALUES('', 2, 'Post name 1', 'my description for post 5');
INSERT INTO posts VALUES('', 2, 'Post name 1', 'my description for post 6');
INSERT INTO posts VALUES('', 1, 'Post name 1', 'my description for post 7');
INSERT INTO posts VALUES('', 1, 'Post name 1', 'my description for post 8');

```
######after use the function find() to select rows using a join in the database

```php
<?php
require 'vendor/autoload.php'; // Autoload
use oop\Core\Table\Table;

$db = new Table();
$response = $db->find('all', [
		'table'      => ['posts' => 'pos'],
		'fields'     => ['pos.id', 'pos.title','pos.description', 'auth.name '],
		'joins'      => [
						'tables'    => ['authors'],
						'alias'     => ['auth'],
						'type'      => ['LEFT'],
						'condition' => ['auth.id' => 'pos.author_id']
		],
		'conditions' => ['author_id' => '?'],
		'order' => ['pos.id' => 'DESC']
	], ['1']
);
echo 'The count of row for each page is : '.$db->rowCount();
var_dump($response);

?>
```
######Pagination exemple with Find();

```php
<?php
require 'vendor/autoload.php'; // Autoload
use oop\Core\Table\Table;

$db = new Table();
$response = $db->find('pagination', [
		'table'      => ['posts' => 'pos'],
		'fields'     => ['pos.id', 'pos.title','pos.description', 'auth.name '],
		'joins'      => [
						'tables'    => ['authors'],
						'alias'     => ['auth'],
						'type'      => ['LEFT'],
						'condition' => ['auth.id' => 'pos.author_id']
		],
		'order' => ['pos.id' => 'DESC']
	]
);

echo 'The count of row for each page is : '.$db->rowCount().'<br/>';
echo 'The total count of rows is : '.$db->countStatement;
var_dump($response);

// Display Pagination
for($i=1; $i<=$db->_paginate_number; $i++){

	if($i == $db->_paginate_currentPage){

		echo ' / '.$i;

	}else{

		echo ' / <a href="index.php?page='.$i.'">'.$i.'</a>';

	}
}

?>
```
###### Update exemple

```php
<?php
require 'vendor/autoload.php'; // Autoload
use oop\Core\Table\Table;

$db = new Table();
$db->table = 'authors'; // Table name

$response = $db->update([
		'fields' => [
			'name' => 'My Name four',
		],
		'conditions' => ['id' => '?']
], [4]);
var_dump($response);
?>
```

###### Delete exemple

```php
<?php
require 'vendor/autoload.php'; // Autoload
use oop\Core\Table\Table;

$db = new Table();
$db->table = 'authors'; // Table name

$response = $db->deleteById(5);
var_dump($response);
?>
```
Delete with conditions

```php
<?php
require 'vendor/autoload.php'; // Autoload
use oop\Core\Table\Table;

$db = new Table();
$db->table = 'authors'; // Table name

$response = $db->delete(['id' => 5]);
var_dump($response);
?>
```
### License

The PHP CRUD is open-source licensed under the [GNU license](http://opensource.org/licenses/GPL-3.0).