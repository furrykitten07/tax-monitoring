<?php

// Railway Database Configuration (MySQL/PostgreSQL - GRATIS $5/bulan)
// Setup Guide: https://railway.app

/*
CARA SETUP RAILWAY:

1. Daftar di https://railway.app dengan GitHub
2. Klik "Start a New Project"
3. Pilih "Deploy MySQL" atau "Deploy PostgreSQL"
4. Wait sampai deploy selesai
5. Klik database yang sudah dibuat
6. Go to "Connect" tab
7. Copy connection details

GRATIS: $5 credit per bulan (cukup untuk hobby project)
*/

return [
    'connections' => [
        'mysql' => [
            'driver' => 'mysql',
            'url' => env('DATABASE_URL'),
            'host' => env('DB_HOST', 'containers-us-west-xxx.railway.app'),
            'port' => env('DB_PORT', '3306'),
            'database' => env('DB_DATABASE', 'railway'),
            'username' => env('DB_USERNAME', 'root'),
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
            ]) : [],
        ],
        
        'pgsql' => [
            'driver' => 'pgsql',
            'url' => env('DATABASE_URL'),
            'host' => env('DB_HOST', 'containers-us-west-xxx.railway.app'),
            'port' => env('DB_PORT', '5432'),
            'database' => env('DB_DATABASE', 'railway'),
            'username' => env('DB_USERNAME', 'postgres'),
            'password' => env('DB_PASSWORD', 'your-password'),
            'charset' => 'utf8',
            'prefix' => '',
            'prefix_indexes' => true,
            'search_path' => 'public',
            'sslmode' => 'require',
        ],
    ]
];

/*
Environment Variables untuk Vercel Dashboard:

# Untuk MySQL:
DB_CONNECTION=mysql
DB_HOST=containers-us-west-xxx.railway.app
DB_PORT=3306
DB_DATABASE=railway
DB_USERNAME=root
DB_PASSWORD=your-railway-password

# Atau untuk PostgreSQL:
DB_CONNECTION=pgsql
DB_HOST=containers-us-west-xxx.railway.app
DB_PORT=5432
DB_DATABASE=railway
DB_USERNAME=postgres
DB_PASSWORD=your-railway-password
*/ 