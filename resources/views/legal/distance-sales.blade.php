@extends('legal.layout')
@section('title', 'Mesafeli Satış Sözleşmesi')

@section('content')
<p>İşbu Mesafeli Satış Sözleşmesi, aşağıda bilgileri yer alan Satıcı ile Alıcı (abone işletme) arasında,
    {{ config('app.name') }} abonelik hizmetinin elektronik ortamda satışına ilişkin olarak düzenlenmiştir.</p>

<h2>1. Satıcı</h2>
<p>
    Unvan: @include('legal.partials.field', ['key' => 'company', 'label' => 'Şirket Ünvanı'])<br>
    Adres: @include('legal.partials.field', ['key' => 'address', 'label' => 'Adres'])<br>
    MERSIS / Sicil No: @include('legal.partials.field', ['key' => 'registry_no', 'label' => 'MERSIS/Sicil No'])<br>
    E-posta: @include('legal.partials.field', ['key' => 'email', 'label' => 'E-posta'])
    @if(config('legal.phone'))<br>Telefon: @include('legal.partials.field', ['key' => 'phone', 'label' => 'Telefon'])@endif
</p>

<h2>2. Alıcı</h2>
<p>Kayıt sırasında verdiği bilgilerle platforma abone olan işletme ve yetkilisidir.</p>

<h2>3. Sözleşme Konusu</h2>
<p>Alıcının, Satıcıya ait platformda seçtiği abonelik planının (aylık veya yıllık) elektronik ortamda
    satın alınması ve bu hizmete ilişkin tarafların hak ve yükümlülüklerinin belirlenmesidir.</p>

<h2>4. Hizmet ve Bedel</h2>
<ul>
    <li>Abonelik planının kapsamı, süresi ve ücreti ödeme sayfasında açıkça gösterilir.</li>
    <li>Tüm ücretler ilgili para biriminde ve vergiler dahil olarak belirtilir.</li>
    <li>Ödeme, anlaşmalı ödeme kuruluşu (ör. PayTR) üzerinden kredi/banka kartı veya havale/EFT ile yapılır.</li>
</ul>

<h2>5. İfa ve Teslim</h2>
<p>Hizmet dijitaldir; ödeme onaylandığı anda abonelik aktifleştirilir ve panel erişimi sağlanır.
    Fiziki teslimat yoktur.</p>

<h2>6. Cayma Hakkı</h2>
<p>
    Mesafeli sözleşmelerde dijital içerik/hizmetlerde, hizmetin ifasına tüketicinin onayıyla başlanması
    halinde cayma hakkı sınırlanabilir. Ödeme sonrası talepler için @include('legal.partials.field', ['key' => 'email', 'label' => 'E-posta']) ile iletişime geçin.
</p>

<h2>7. İptal ve İade</h2>
<ul>
    <li>Aboneliğinizi dilediğiniz zaman iptal edebilirsiniz; iptal, mevcut dönem sonunda yürürlüğe girer.</li>
    <li>Kullanılmaya başlanan dönem için, mevzuatın öngördüğü haller saklı kalmak kaydıyla iade yapılmayabilir.</li>
    <li>Satıcı kaynaklı bir hizmet kusuru halinde ilgili dönem bedeli iade edilir veya süre uzatılır.</li>
</ul>

<h2>8. Fatura</h2>
<p>Ödemelere ilişkin fatura, mevzuata uygun olarak düzenlenir ve Alıcının kayıtlı e-posta adresine iletilir.</p>

<h2>9. Uyuşmazlık</h2>
<p>Bu sözleşmeden doğan uyuşmazlıklarda yürürlükteki tüketici mevzuatı ve yetkili tüketici hakem heyetleri
    ile mahkemeleri yetkilidir.</p>

<p class="updated" style="margin-top:24px">Alıcı, ödeme adımında bu sözleşmeyi okuyup kabul ettiğini beyan eder.</p>
@endsection
