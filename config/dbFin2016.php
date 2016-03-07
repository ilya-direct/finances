<?php

return [
    'class' => \yii\db\Connection::class,
    'dsn' => 'pgsql:host=localhost;dbname=finance',
    'username' => 'postgres',
    'password' => 'postgres',
    'charset' => 'utf8',
    'schemaMap' => [
        'pgsql'=> [
            'class'=>\yii\db\pgsql\Schema::class,
            'defaultSchema' => 'public' //specify your schema here
        ]
    ],

];
