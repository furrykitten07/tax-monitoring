<?php

// Supabase Database Configuration (PostgreSQL - GRATIS SELAMANYA)
// Setup Guide: https://supabase.com

/*
CARA SETUP SUPABASE:

1. Daftar di https://supabase.com (gratis)
2. Klik "New Project"
3. Pilih organization
4. Nama project: tax-monitoring
5. Database password: buat password yang kuat
6. Region: Southeast Asia (Singapore)
7. Wait sampai database ready (2-3 menit)

8. Setelah ready, klik Settings > Database
9. Copy "Connection string" di bagian Connection pooling
10. Format: postgresql://postgres.xxx:[YOUR-PASSWORD]@xxx.pooler.supabase.com:6543/postgres

*/

return [
    'connections' => [
        'pgsql' => [
            'driver' => 'pgsql',
            'url' => env('DATABASE_URL'),
            'host' => env('DB_HOST', 'db.xxx.supabase.co'),
            'port' => env('DB_PORT', '5432'),
            'database' => env('DB_DATABASE', 'postgres'),
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

APP_NAME=Tax Monitoring System
APP_ENV=production
APP_KEY=base64:YOUR_GENERATED_KEY
APP_DEBUG=false
APP_URL=https://your-app.vercel.app

DB_CONNECTION=pgsql
DB_HOST=db.xxx.supabase.co
DB_PORT=5432
DB_DATABASE=postgres
DB_USERNAME=postgres
DB_PASSWORD=your-supabase-password

FILAMENT_ADMIN_EMAIL=fikri@a.com
FILAMENT_ADMIN_PASSWORD=fikri

SESSION_DRIVER=cookie
CACHE_DRIVER=array
QUEUE_CONNECTION=sync
*/ 