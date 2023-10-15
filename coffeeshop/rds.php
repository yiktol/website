<?php

$secret = require 'rds-config.php';

define('DB_SERVER', $secret["DB_SERVER"]);
define('DB_USERNAME', $secret["DB_USERNAME"]);
define('DB_PASSWORD', $secret["DB_PASSWORD"]);
define('DB_DATABASE', $secret["DB_DATABASE"]);


// print(DB_SERVER);
// print(DB_USERNAME);
// print(DB_PASSWORD); 
// print(DB_DATABASE); 

?>