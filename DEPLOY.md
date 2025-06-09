# Deploy Laravel Tax Monitoring ke Vercel

## Prerequisites
1. Akun Vercel (https://vercel.com)
2. Akun GitHub
3. Database MySQL online (PlanetScale, Railway, atau Aiven)

## Step 1: Setup Database Online

### Option A: PlanetScale (Gratis)
1. Daftar di https://planetscale.com
2. Buat database baru
3. Dapatkan connection string

### Option B: Railway (Gratis)
1. Daftar di https://railway.app  
2. Deploy MySQL database
3. Dapatkan connection details

### Option C: Aiven (Gratis)
1. Daftar di https://aiven.io
2. Buat MySQL service
3. Dapatkan connection string

## Step 2: Push ke GitHub
```bash
git init
git add .
git commit -m "Initial commit"
git branch -M main
git remote add origin https://github.com/USERNAME/REPO-NAME.git
git push -u origin main
```

## Step 3: Deploy ke Vercel

### Via Dashboard Vercel:
1. Login ke https://vercel.com
2. Klik "New Project"
3. Import repository GitHub
4. Framework Preset: **Other**

### Environment Variables:
Tambahkan di Vercel Dashboard > Settings > Environment Variables:

```
APP_NAME=Tax Monitoring System
APP_ENV=production
APP_KEY=base64:YOUR_GENERATED_KEY (dari artisan key:generate)
APP_DEBUG=false
APP_URL=https://your-app-name.vercel.app

DB_CONNECTION=mysql
DB_HOST=your-database-host
DB_PORT=3306
DB_DATABASE=your-database-name
DB_USERNAME=your-database-username
DB_PASSWORD=your-database-password

FILAMENT_ADMIN_EMAIL=fikri@a.com
FILAMENT_ADMIN_PASSWORD=fikri

SESSION_DRIVER=cookie
CACHE_DRIVER=array
QUEUE_CONNECTION=sync
```

## Step 4: Setelah Deploy

1. **Migrate Database:**
   - Via Vercel terminal atau local:
   ```bash
   php artisan migrate --force
   ```

2. **Create Admin User:**
   ```bash
   php artisan make:filament-user
   ```

## Step 5: Custom Domain (Opsional)
1. Beli domain
2. Setup DNS
3. Add domain di Vercel

## Tips:
- Vercel auto-deploy setiap push ke GitHub
- Check logs di Vercel Dashboard jika ada error
- Database harus online/cloud, tidak bisa SQLite
- File storage menggunakan cloud (S3, Cloudinary, etc.)

## Troubleshooting:

### Error "Route not found":
- Pastikan vercel.json sudah benar
- Check di Vercel Functions tab

### Error Database:
- Pastikan environment variables benar
- Test connection ke database

### Error 500:
- Check Vercel Function logs
- Pastikan .env variables sudah set

## Alternative: Deploy ke VPS
Jika Vercel bermasalah, bisa deploy ke:
- DigitalOcean App Platform
- Railway 
- Heroku
- VPS dengan Laravel Forge 