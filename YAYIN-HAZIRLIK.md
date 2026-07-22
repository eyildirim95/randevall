# Randevall — Yayın (Production) Hazırlık Rehberi

Bu doküman projeyi ilk kez canlıya alırken izlenecek adımları ve **zamanlanmış görev / kuyruk** kurulumunu içerir. Adımları sırayla uygulayın.

---

## 1. Sunucu gereksinimleri
- PHP 8.3+ (eklentiler: `pdo_mysql, openssl, mbstring, curl, intl, gd, zip, sodium`)
- MySQL 8+ (veya MariaDB 10.6+)
- Composer 2
- Node 20+ (yalnızca `npm run build` için; sunucuda derlenmiş asset varsa gerekmez)
- Web sunucu: Nginx/Apache + **HTTPS sertifikası (zorunlu)**

## 2. İlk kurulum
```bash
git clone <repo> reserup && cd reserup
composer install --no-dev --optimize-autoloader
cp .env.production.example .env      # sonra .env içindeki DEĞERLERİ DOLDURUN
php artisan key:generate
php artisan migrate --force
php artisan db:seed --force          # planlar + süper admin (yalnızca ilk kurulumda)
npm ci && npm run build              # asset'ler (public/build)
php artisan storage:link
```

## 3. Config önbellekleme (her deploy'da)
```bash
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan event:cache
```
> Config cache'lendiğinde `.env` değişiklikleri okunmaz; `.env` değiştirirseniz `php artisan config:cache` tekrar çalıştırın.

## 4. Dosya izinleri (Linux)
```bash
chown -R www-data:www-data storage bootstrap/cache
chmod -R 775 storage bootstrap/cache
```

---

## 5. ⏰ Zamanlanmış görevler (KRİTİK)

Randevu hatırlatmaları, doğum günü mesajları ve **kuyruk işleme (WhatsApp/e-posta)** Laravel scheduler ile çalışır. Scheduler kurulmazsa **hiçbir bildirim gönderilmez.**

### Linux (cron)
`crontab -e` ile ekleyin:
```cron
* * * * * cd /path/to/reserup && php artisan schedule:run >> /dev/null 2>&1
```

### Windows (Task Scheduler)
Her dakika çalışan bir görev:
```powershell
# Yönetici PowerShell'de (yolları kendinize göre düzenleyin):
$action  = New-ScheduledTaskAction -Execute "php" -Argument "artisan schedule:run" -WorkingDirectory "C:\path\to\reserup"
$trigger = New-ScheduledTaskTrigger -Once -At (Get-Date) -RepetitionInterval (New-TimeSpan -Minutes 1)
Register-ScheduledTask -TaskName "Randevall Scheduler" -Action $action -Trigger $trigger -RunLevel Highest
```

`routes/console.php` içindeki tanımlı işler:
- `reserup:send-reminders` — her 15 dk (randevu hatırlatma)
- `reserup:send-birthday-greetings` — her gün 09:00
- `queue:work --stop-when-empty` — her dakika (kuyruğu işler)

## 6. 🔁 Kuyruk worker (önerilen — yüksek trafikte)

Scheduler'daki `queue:work` küçük ölçek için yeterlidir. Yoğunlukta ayrı kalıcı worker kullanın:

### Linux (Supervisor) — `/etc/supervisor/conf.d/reserup-worker.conf`
```ini
[program:reserup-worker]
process_name=%(program_name)s_%(process_num)02d
command=php /path/to/reserup/artisan queue:work --sleep=3 --tries=3 --max-time=3600
autostart=true
autorestart=true
numprocs=1
user=www-data
redirect_stderr=true
stdout_logfile=/path/to/reserup/storage/logs/worker.log
stopwaitsecs=3600
```
```bash
supervisorctl reread && supervisorctl update && supervisorctl start reserup-worker:*
```
> Ayrı worker kullanırsanız `routes/console.php` içindeki `queue:work` satırını kaldırabilirsiniz (çakışmayı önlemek için).

---

## 7. Yayın öncesi kontrol listesi

- [ ] `.env` → `APP_ENV=production`, `APP_DEBUG=false`, gerçek `APP_URL` (https)
- [ ] `APP_KEY` üretildi
- [ ] Gerçek SMTP/e-posta sağlayıcısı ayarlandı (varsayılan `log` mail göndermez)
- [ ] `SESSION_SECURE_COOKIE=true`
- [ ] MySQL kullanıcısına güçlü parola
- [ ] `php artisan migrate --force` çalıştı
- [ ] Scheduler cron kuruldu (madde 5) — **doğrula:** `php artisan schedule:list`
- [ ] `npm run build` yapıldı, `public/build` mevcut
- [ ] HTTPS sertifikası aktif
- [ ] Süper Admin > Ayarlar: WhatsApp (Meta/Twilio) + PayTR bilgileri girildi
- [ ] En az bir abonelik planı tanımlı ve aktif
- [ ] Yasal sayfalar dolduruldu (KVKK / Koşullar / Mesafeli Satış) — `resources/views/legal/`
- [ ] Test siparişi + test randevusu ile uçtan uca doğrulama

## 8. Yayın sonrası bakım
```bash
# Yeni deploy
git pull
composer install --no-dev --optimize-autoloader
php artisan migrate --force
npm ci && npm run build
php artisan config:cache route:cache view:cache
php artisan queue:restart   # worker'ları yeni kodla yeniler
```
