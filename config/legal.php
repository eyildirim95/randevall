<?php

/*
|--------------------------------------------------------------------------
| Yasal / Şirket Bilgileri
|--------------------------------------------------------------------------
| YAYINDAN ÖNCE bu değerleri gerçek şirket bilgilerinizle doldurun.
| Yasal sayfalar (KVKK, Koşullar, Mesafeli Satış) bu değerleri kullanır.
| Boş bırakılan alanlar sayfada "[DOLDURULACAK]" olarak vurgulanır.
*/

return [
    'updated_at' => '2026-07-05',

    // Ticari ünvan (ör. "Randevall Yazılım Ltd.")
    'company' => env('LEGAL_COMPANY', ''),

    // Adres
    'address' => env('LEGAL_ADDRESS', ''),

    // MERSIS No / Ticaret Sicil
    'registry_no' => env('LEGAL_REGISTRY_NO', ''),

    // Vergi dairesi / no
    'tax_office' => env('LEGAL_TAX_OFFICE', ''),

    // İletişim
    'email' => env('LEGAL_EMAIL', 'bilgi@randevall.com'),

    // Ücretsiz deneme süresi (gün) — 0 ise deneme yok
    'trial_days' => (int) env('LEGAL_TRIAL_DAYS', 0),
    'phone' => env('LEGAL_PHONE', ''),

    // KEP adresi (varsa)
    'kep' => env('LEGAL_KEP', ''),
];
