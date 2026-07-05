@extends('legal.layout')
@section('title', 'Gizlilik Politikası ve KVKK Aydınlatma Metni')

@section('content')
<p>
    @include('legal.partials.field', ['key' => 'company', 'label' => 'Şirket Ünvanı']) ("BooKıbrıs") olarak
    kişisel verilerinizin güvenliğine önem veriyoruz. Bu metin, 6698 sayılı Kişisel Verilerin Korunması
    Kanunu ("KVKK") kapsamında veri sorumlusu sıfatıyla kişisel verilerinizin nasıl işlendiğini açıklar.
</p>

<h2>1. Veri Sorumlusu</h2>
<p>
    Unvan: @include('legal.partials.field', ['key' => 'company', 'label' => 'Şirket Ünvanı'])<br>
    Adres: @include('legal.partials.field', ['key' => 'address', 'label' => 'Adres'])<br>
    İletişim: @include('legal.partials.field', ['key' => 'email', 'label' => 'E-posta'])
</p>

<h2>2. İşlenen Kişisel Veriler</h2>
<ul>
    <li><strong>Kimlik &amp; iletişim:</strong> ad-soyad, telefon, e-posta.</li>
    <li><strong>İşletme bilgileri:</strong> işletme adı, adres, sektör (kayıt olan işletmeler için).</li>
    <li><strong>Randevu verileri:</strong> hizmet, tarih/saat, personel, notlar.</li>
    <li><strong>Ödeme verileri:</strong> abonelik/ödeme kayıtları. Kart bilgileri BooKıbrıs tarafından
        saklanmaz; ödeme altyapısı sağlayıcısı (ör. PayTR) üzerinden işlenir.</li>
    <li><strong>Teknik veriler:</strong> IP adresi, oturum ve kullanım kayıtları (güvenlik ve loglama).</li>
</ul>

<h2>3. İşleme Amaçları</h2>
<ul>
    <li>Randevu oluşturma, yönetme ve hatırlatma bildirimleri gönderme</li>
    <li>Abonelik ve ödeme süreçlerinin yürütülmesi</li>
    <li>Müşteri destek taleplerinin karşılanması</li>
    <li>Hizmet güvenliği, dolandırıcılığın önlenmesi ve yasal yükümlülükler</li>
</ul>

<h2>4. Hukuki Sebepler</h2>
<p>Verileriniz; sözleşmenin kurulması/ifası, hukuki yükümlülük, meşru menfaat ve gerektiğinde açık rıza
    hukuki sebeplerine dayanılarak işlenir (KVKK m.5).</p>

<h2>5. Veri İşleyen (Müşteri Verileri)</h2>
<p>
    İşletmelerin platforma girdiği son-müşteri verileri bakımından <strong>veri sorumlusu ilgili işletmedir</strong>;
    BooKıbrıs bu verileri işletme adına işleyen (veri işleyen) sıfatıyla saklar ve işler. İşletmeler,
    müşterilerinden gerekli aydınlatma ve onayları almakla yükümlüdür.
</p>

<h2>6. Aktarım</h2>
<p>Kişisel veriler; hizmetin sunulması için gereken barındırma, e-posta, SMS/WhatsApp ve ödeme
    sağlayıcıları ile yasal olarak yetkili kurumlarla, amaçla sınırlı olarak paylaşılabilir.</p>

<h2>7. Saklama Süresi</h2>
<p>Veriler, işleme amacının gerektirdiği ve mevzuatın öngördüğü süre boyunca saklanır; süre sonunda
    silinir, yok edilir veya anonim hale getirilir.</p>

<h2>8. Haklarınız (KVKK m.11)</h2>
<p>Kişisel verilerinizle ilgili olarak; işlenip işlenmediğini öğrenme, bilgi talep etme, düzeltilmesini
    veya silinmesini isteme, işlemeye itiraz etme ve zararın giderilmesini talep etme haklarına sahipsiniz.
    Taleplerinizi @include('legal.partials.field', ['key' => 'email', 'label' => 'E-posta']) adresine iletebilirsiniz.</p>

<h2>9. Çerezler</h2>
<p>Sitemiz oturum ve tercih çerezleri kullanır. Ayrıntılar için
    <a href="{{ route('legal.cookies') }}">Çerez Politikası</a>’nı inceleyin.</p>
@endsection
