@extends('legal.layout')
@section('title', 'Kullanım Koşulları')

@section('content')
<p>
    Bu Kullanım Koşulları ("Koşullar"), @include('legal.partials.field', ['key' => 'company', 'label' => 'Şirket Ünvanı'])
    ("BooKıbrıs", "biz") tarafından sunulan {{ config('app.name') }} randevu yönetim platformunun ("Hizmet")
    kullanımına ilişkin şartları düzenler. Hizmete kayıt olarak veya kullanarak bu Koşulları kabul etmiş sayılırsınız.
</p>

<h2>1. Hizmetin Tanımı</h2>
<p>
    BooKıbrıs; işletmelerin online randevu alması, müşteri ve personel yönetimi, hatırlatma mesajları,
    gelir-gider takibi ve benzeri özellikler sunan bulut tabanlı (SaaS) bir yazılım hizmetidir.
    Hizmet abonelik modeliyle sunulur ve deneme süresi içerir.
</p>

<h2>2. Hesap ve Sorumluluk</h2>
<ul>
    <li>Kayıt sırasında verdiğiniz bilgilerin doğru, güncel ve eksiksiz olduğunu taahhüt edersiniz.</li>
    <li>Hesap güvenliğinizden (şifre dahil) ve hesabınız altında gerçekleşen işlemlerden siz sorumlusunuz.</li>
    <li>İşletmeniz aracılığıyla topladığınız müşteri verilerinin işlenmesinde veri sorumlusu sizsiniz;
        BooKıbrıs bu verileri sizin adınıza işleyen konumundadır (bkz. Gizlilik &amp; KVKK).</li>
</ul>

<h2>3. Abonelik, Deneme ve Ödeme</h2>
<ul>
    <li>Deneme süresi sonunda hizmete devam etmek için uygun bir abonelik planı seçilmeli ve ödeme yapılmalıdır.</li>
    <li>Abonelik ücretleri seçtiğiniz plana ve döneme (aylık/yıllık) göre belirlenir ve ödeme sayfasında gösterilir.</li>
    <li>Ödeme alınmadığı takdirde panel erişimi kısıtlanabilir; verileriniz makul bir süre saklanır.</li>
    <li>Ayrıntılı satış/iade şartları için <a href="{{ route('legal.distance-sales') }}">Mesafeli Satış Sözleşmesi</a>’ni inceleyin.</li>
</ul>

<h2>4. Kabul Edilebilir Kullanım</h2>
<p>Hizmeti yasa dışı amaçlarla, spam/istenmeyen mesaj göndermek için, üçüncü kişilerin haklarını ihlal edecek
    veya sistemin güvenliğini/bütünlüğünü tehlikeye atacak şekilde kullanamazsınız. İhlal halinde hesabınız
    askıya alınabilir veya kapatılabilir.</p>

<h2>5. Mesajlaşma (WhatsApp / E-posta)</h2>
<p>Müşterilerinize gönderilen hatırlatma ve bilgilendirme mesajlarının içeriğinden ve ilgili kişilerin
    onayını almış olmaktan siz sorumlusunuz. BooKıbrıs yalnızca teknik altyapıyı sağlar.</p>

<h2>6. Hizmet Sürekliliği</h2>
<p>Hizmeti kesintisiz sunmak için makul çaba gösteririz; ancak bakım, güncelleme veya öngörülemeyen
    nedenlerle geçici kesintiler yaşanabilir. Planlı bakımları mümkün olduğunca önceden duyururuz.</p>

<h2>7. Sorumluluğun Sınırlandırılması</h2>
<p>Hizmet "olduğu gibi" sunulur. Yürürlükteki mevzuatın izin verdiği ölçüde, dolaylı zararlardan sorumlu
    değiliz. Toplam sorumluluğumuz, ilgili talebin doğduğu ayı önceleyen 12 ayda ödediğiniz abonelik bedeliyle sınırlıdır.</p>

<h2>8. Fesih</h2>
<p>Aboneliğinizi dilediğiniz zaman iptal edebilirsiniz. Koşulları ihlal etmeniz halinde hesabınızı
    askıya alma veya sonlandırma hakkımız saklıdır.</p>

<h2>9. Değişiklikler</h2>
<p>Bu Koşulları zaman zaman güncelleyebiliriz. Önemli değişiklikleri e-posta veya panel üzerinden bildiririz.
    Değişiklik sonrası kullanımınız güncel Koşulları kabul ettiğiniz anlamına gelir.</p>

<h2>10. Uygulanacak Hukuk ve İletişim</h2>
<p>Bu Koşullar Kuzey Kıbrıs Türk Cumhuriyeti / Türkiye Cumhuriyeti mevzuatına tabidir.
    Sorularınız için: @include('legal.partials.field', ['key' => 'email', 'label' => 'E-posta'])
    @if(config('legal.phone')) — @include('legal.partials.field', ['key' => 'phone', 'label' => 'Telefon']) @endif
</p>
@endsection
