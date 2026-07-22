# Randevall — Multi-Tenant Randevu Sistemi

Türkiye pazarı için randevuyla çalışan işletmelere (berber, kuaför, güzellik salonu, klinik...) yönelik SaaS randevu yönetim sistemi. Laravel 13 + MySQL + Bootstrap 5 (Lahomes admin teması).

## Özellikler

- **Multi-tenant mimari** — işletmeler `randevall.com/{slug}` ile ayrışır; veri izolasyonu ORM seviyesinde global scope + scoped route binding ile sağlanır
- **Tek giriş noktası** — süper admin, işletme sahibi ve personel aynı `/giris` ekranından girer, rolüne göre yönlendirilir
- **Akıllı takvim** — FullCalendar; boş alana tıklayınca randevu oluşturma, sürükle-bırak ile taşıma/uzatma, personel filtresi, durum yönetimi
- **Online rezervasyon** — `randevall.com/{slug}` public sayfası: hizmet → personel → uygun saat → bilgiler; çalışma saati/mola/kapatma/çakışma kontrolü
- **WhatsApp + e-posta bildirimleri** — onay, hatırlatma (zamanlanmış), iptal; Meta Cloud API / Twilio / log provider
- **Gelir-gider tablosu** — tamamlanan randevu otomatik gelire işlenir; kategori bazlı gider takibi, CSV dışa aktarım
- **Sadık müşteri sistemi** — ziyaret başına puan, eşikte ödül, manuel düzeltme
- **Takvim kapatma** — işletme geneli veya personel bazlı tatil/izin aralıkları
- **Raporlama** — günlük seri, personel performansı, hizmet dağılımı, kaynak analizi, en iyi müşteriler
- **Süper admin paneli** — işletme yönetimi, planlar, ödemeler (PayTR + havale/EFT), duyurular, impersonation, mesaj logları
- **Landing page** — Türkçe pazarlama sayfası + self-servis kayıt

## Kurulum

```bash
composer install
npm install
cp .env.example .env   # DB ayarlarını yapın
php artisan key:generate
php artisan migrate --seed
npm run build
php artisan storage:link
php artisan serve
```

Zamanlanmış işler (hatırlatma + kuyruk) için:

```bash
php artisan schedule:work    # gelistirme
# prod: * * * * * php artisan schedule:run
```

## Seed Hesaplar

| Rol | E-posta | Şifre |
|---|---|---|
| Süper Admin | admin@randevall.com | admin123! |
| İşletme Sahibi | ornek@randevall.com | demo123! |

- İşletme paneli: `/ornek-berber/panel`
- Online rezervasyon: `/ornek-berber`
- Süper admin: `/admin`

## Mimari Notlar

- `app/Support/Tenancy.php` — aktif tenant erişimi; `IdentifyTenant` middleware path'ten çözer
- `app/Models/Concerns/BelongsToTenant.php` — global scope + otomatik `business_id` (IDOR koruması)
- Route'larda `scopeBindings()` — alt kayıtlar daima işletme ilişkisi üzerinden çözülür
- `app/Services/Booking/` — müsaitlik hesaplama ve randevu yaşam döngüsü (çifte rezervasyona karşı DB kilidi)
- `app/Services/Messaging/` — WhatsApp provider pattern (Meta/Twilio/Log)
- `app/Services/Payments/` — ödeme provider pattern (PayTR iFrame + Havale/EFT, imza doğrulamalı callback)
- Hassas alanlar (`whatsapp_api_key` encrypted cast, `is_super_admin`, plan alanları) mass-assignment dışında tutulur

## Güvenlik

- Login rate limit (5 deneme/e-posta+IP), session regeneration, honeypot, public token'lar UUID
- Tenant izolasyonu ve IDOR testleri geçti (403/404)
- PayTR callback HMAC imza doğrulaması, CSRF muafiyeti yalnızca callback endpoint'inde
