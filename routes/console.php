<?php

use Illuminate\Support\Facades\Schedule;

// Randevu hatirlatmalari: her 15 dakikada bir kontrol edilir
Schedule::command('reserup:send-reminders')->everyFifteenMinutes();

// Kuyruk isleri (whatsapp/mail) — ayri worker yoksa schedule uzerinden islenir
Schedule::command('queue:work --stop-when-empty --max-time=50')
    ->everyMinute()
    ->withoutOverlapping();
