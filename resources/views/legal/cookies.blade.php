@extends('legal.layout')
@section('title', 'Çerez Politikası')

@section('content')
<p>{{ config('app.name') }} olarak web sitemizde deneyiminizi iyileştirmek ve hizmeti güvenli sunmak için
    çerezler (cookies) kullanıyoruz. Bu politika hangi çerezleri neden kullandığımızı açıklar.</p>

<h2>1. Çerez Nedir?</h2>
<p>Çerezler, ziyaret ettiğiniz siteler tarafından tarayıcınıza kaydedilen küçük metin dosyalarıdır.
    Oturumunuzu sürdürmek ve tercihlerinizi hatırlamak için kullanılır.</p>

<h2>2. Kullandığımız Çerez Türleri</h2>
<ul>
    <li><strong>Zorunlu çerezler:</strong> Oturum açma, güvenlik (CSRF) ve temel işlevler için gereklidir.
        Bu çerezler olmadan site çalışmaz; devre dışı bırakılamaz.</li>
    <li><strong>Tercih çerezleri:</strong> Dil ve arayüz tercihlerinizi hatırlar.</li>
    <li><strong>Analitik çerezler (varsa):</strong> Sitenin nasıl kullanıldığını anonim olarak ölçmek için
        kullanılabilir. Bunlar için onayınız alınır.</li>
</ul>

<h2>3. Çerezleri Yönetme</h2>
<p>Tarayıcı ayarlarınızdan çerezleri silebilir veya engelleyebilirsiniz. Ancak zorunlu çerezleri
    engellerseniz platforma giriş yapamayabilirsiniz.</p>

<h2>4. İletişim</h2>
<p>Çerez kullanımı hakkında sorularınız için:
    @include('legal.partials.field', ['key' => 'email', 'label' => 'E-posta'])</p>
@endsection
