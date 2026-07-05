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

    // Ticari ünvan (ör. "Reserup Yazılım Ltd.")
    'company' => env('LEGAL_COMPANY', ''),

    // Adres
    'address' => env('LEGAL_ADDRESS', ''),

    // MERSIS No / Ticaret Sicil (TR) veya şirket kayıt no (Kıbrıs)
    'registry_no' => env('LEGAL_REGISTRY_NO', ''),

    // Vergi dairesi / no
    'tax_office' => env('LEGAL_TAX_OFFICE', ''),

    // İletişim
    'email' => env('LEGAL_EMAIL', 'bilgi@reserup.com'),
    'phone' => env('LEGAL_PHONE', ''),

    // KEP adresi (varsa)
    'kep' => env('LEGAL_KEP', ''),
];
