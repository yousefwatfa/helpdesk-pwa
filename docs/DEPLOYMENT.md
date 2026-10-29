<div dir="rtl">

# النشر على خادم (Ubuntu)

الخطوات لخادم Ubuntu 22.04/24.04 مع Nginx أو Apache وقاعدة MySQL/MariaDB. غيّر `helpdesk.example.com` لاسم النطاق الخاص بكم.

## 1. الحزم

```bash
sudo apt update
sudo apt install -y nginx mysql-server unzip git \
  php8.3-fpm php8.3-cli php8.3-mysql php8.3-mbstring php8.3-xml php8.3-curl \
  php8.3-zip php8.3-gd php8.3-bcmath php8.3-intl
# اختياري، فقط مع LDAP:
sudo apt install -y php8.3-ldap
```

ثبّت Composer و Node 20 (من nodesource أو nvm).

## 2. قاعدة البيانات

```bash
sudo mysql
```

```sql
CREATE DATABASE helpdesk CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER 'helpdesk'@'localhost' IDENTIFIED BY 'كلمة-مرور-قوية';
GRANT ALL PRIVILEGES ON helpdesk.* TO 'helpdesk'@'localhost';
FLUSH PRIVILEGES;
```

## 3. الكود

```bash
sudo mkdir -p /var/www/helpdesk && sudo chown $USER:www-data /var/www/helpdesk
git clone <repo-url> /var/www/helpdesk
cd /var/www/helpdesk
composer install --no-dev --optimize-autoloader
npm ci && npm run build
cp .env.example .env
php artisan key:generate
```

عدّل `.env`: `APP_URL`، بيانات `DB_*`، `APP_TIMEZONE`، و (اختياري) LDAP.

```bash
php artisan helpdesk:install
php artisan webpush:vapid           # مفاتيح إشعارات الهاتف
php artisan storage:link
php artisan config:cache && php artisan route:cache && php artisan view:cache

sudo chown -R $USER:www-data storage bootstrap/cache public
sudo chmod -R ug+rwx storage bootstrap/cache
mkdir -p public/branding && sudo chown -R www-data:www-data public/branding   # الشعار والأيقونات المرفوعة
```

## 4. خادم الويب (Nginx)

`/etc/nginx/sites-available/helpdesk`:

```nginx
server {
    listen 80;
    server_name helpdesk.example.com;
    root /var/www/helpdesk/public;
    index index.php;
    client_max_body_size 20M;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    # the service worker must never be cached by the browser
    location = /sw.js {
        add_header Cache-Control "no-cache";
        try_files $uri =404;
    }

    location ~ \.php$ {
        include snippets/fastcgi-php.conf;
        fastcgi_pass unix:/run/php/php8.3-fpm.sock;
    }
}
```

```bash
sudo ln -s /etc/nginx/sites-available/helpdesk /etc/nginx/sites-enabled/
sudo nginx -t && sudo systemctl reload nginx
```

مع **Apache**: اجعل `DocumentRoot` هو `/var/www/helpdesk/public` مع `AllowOverride All` وفعّل `a2enmod rewrite`.

## 5. HTTPS (مطلوب للتثبيت كتطبيق وللإشعارات)

```bash
sudo apt install -y certbot python3-certbot-nginx
sudo certbot --nginx -d helpdesk.example.com
```

خادم داخلي غير متاح من الإنترنت؟ استخدم تحقق DNS (`certbot certonly --manual --preferred-challenges dns`) أو شهادة من جهة داخلية يثق بها جميع الأجهزة.

## 6. التحديث الفوري في المتصفح (اختياري: Reverb)

في `.env`: `BROADCAST_CONNECTION=reverb` واملأ `REVERB_*`، ثم `npm run build` وشغّل Reverb كخدمة:

```ini
# /etc/systemd/system/helpdesk-reverb.service
[Unit]
Description=Help desk Reverb
After=network.target

[Service]
User=www-data
WorkingDirectory=/var/www/helpdesk
ExecStart=/usr/bin/php artisan reverb:start --host=127.0.0.1 --port=8080
Restart=always

[Install]
WantedBy=multi-user.target
```

ومرّر `/app` و`/apps` من Nginx إلى `127.0.0.1:8080` (WebSocket). بدون Reverb يعمل كل شيء، والإشعارات تظهر عند تحديث الصفحة، وإشعارات الهاتف تعمل بشكل طبيعي.

## 7. التحديثات

```bash
cd /var/www/helpdesk
git pull
composer install --no-dev --optimize-autoloader
npm ci && npm run build
php artisan migrate --force
php artisan config:cache && php artisan route:cache && php artisan view:cache
```

بعد تغيير `public/sw.js` ارفع رقم `VERSION` داخله حتى تتحدث الأجهزة المثبّت عليها التطبيق.

</div>
