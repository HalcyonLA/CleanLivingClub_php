<?php

return [
	'class' => 'yii\db\Connection',
	'dsn' => 'mysql:host=' . DB_HOST . ';port=' . DB_PORT . ';dbname=' . DB_NAME,
	'username' => DB_USER,
	'password' => DB_PASSWORD,
	'charset' => 'utf8mb4',
	'tablePrefix' => 'cl_',
];
