<?php

// PlanetScale Database Configuration untuk Production
// Copy konfigurasi ini ke Environment Variables di Vercel

return [
    'connections' => [
        'mysql' => [
            'driver' => 'mysql',
            'url' => env('DATABASE_URL'),
            'host' => env('DB_HOST', 'aws.connect.psdb.cloud'),
            'port' => env('DB_PORT', '3306'),
            'database' => env('DB_DATABASE', 'tax-monitoring'),
            'username' => env('DB_USERNAME', 'your-username'),
            'password' => env('DB_PASSWORD', 'your-password'),
            'unix_socket' => env('DB_SOCKET', ''),
            'charset' => 'utf8mb4',
            'collation' => 'utf8mb4_unicode_ci',
            'prefix' => '',
            'prefix_indexes' => true,
            'strict' => true,
            'engine' => null,
            'options' => extension_loaded('pdo_mysql') ? array_filter([
                PDO::MYSQL_ATTR_SSL_CA => env('MYSQL_ATTR_SSL_CA'),
                PDO::MYSQL_ATTR_SSL_VERIFY_SERVER_CERT => false,
            ]) : [],
            'sslmode' => env('DB_SSLMODE', 'require'),
        ],
    ]
];

/*
Environment Variables untuk Vercel Dashboard:

APP_NAME=Tax Monitoring System
APP_ENV=production
APP_KEY=base64:YOUR_GENERATED_KEY
APP_DEBUG=false
APP_URL=https://your-app.vercel.app

DB_CONNECTION=mysql
DB_HOST=aws.connect.psdb.cloud
DB_PORT=3306
DB_DATABASE=tax-monitoring
DB_USERNAME=your-planetscale-username
DB_PASSWORD=your-planetscale-password
DB_SSLMODE=require

FILAMENT_ADMIN_EMAIL=fikri@a.com
FILAMENT_ADMIN_PASSWORD=fikri

SESSION_DRIVER=cookie
CACHE_DRIVER=array
QUEUE_CONNECTION=sync
*/ 