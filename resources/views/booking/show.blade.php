@extends('layouts.auth', ['title' => $business->name.' — Online Randevu'])

@section('content')
    <div class="col-xl-6 col-lg-8">
        <div class="card auth-card">
            <div class="card-body p-4">
                {{-- Isletme basligi --}}
                <div class="text-center mb-4">
                    @if($business->logo_path)
                        <img src="{{ $business->logo_url }}" alt="{{ $business->name }}" height="64" class="rounded mb-2">
                    @endif
                    <h3 class="mb-1">{{ $business->name }}</h3>
                    @if($business->address || $business->city)
                        <p class="text-muted mb-1"><i class="ri-map-pin-line me-1"></i>{{ $business->address }} {{ $business->city }}</p>
                    @endif
                    @if($business->phone)
                        <p class="text-muted mb-0"><i class="ri-phone-line me-1"></i>{{ $business->phone }}</p>
                    @endif
                    @if($ratingStats && $ratingStats->total >= 3)
                        <p class="mb-0 mt-1">
                            <span class="text-warning">★ {{ number_format($ratingStats->avg, 1) }}</span>
                            <small class="text-muted">({{ $ratingStats->total }} değerlendirme)</small>
                        </p>
                    @endif
                </div>

                @include('layouts.partials.alerts')

                @if($services->isEmpty())
                    <div class="alert alert-info text-center">Şu anda online randevuya açık hizmet bulunmuyor. Lütfen işletmeyi arayın.</div>
                @else
                    <form method="POST" action="{{ route('booking.store', $business) }}" id="booking-form"
                          data-slots-url="{{ route('booking.slots', $business) }}"
                          data-waitlist-url="{{ route('booking.waitlist', $business) }}">
                        @csrf

                        {{-- 1. Hizmet --}}
                        <h5 class="mb-2"><span class="badge bg-primary me-1">1</span> Hizmet Seçin</h5>
                        <div class="mb-3">
                            <select name="service_id" id="bk-service" class="form-select" required
                                    data-msg-required="Lütfen bir hizmet seçin.">
                                <option value="">Hizmet seçin...</option>
                                @foreach($services as $service)
                                    <option value="{{ $service->id }}"
                                            data-staff='@json($service->staff->map(fn($s) => ['id' => $s->id, 'name' => $s->name]))'>
                                        {{ $service->name }} — {{ $service->duration_minutes }} dk · {{ number_format($service->price, 0, ',', '.') }} {{ $business->currency }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        {{-- 2. Personel --}}
                        <h5 class="mb-2"><span class="badge bg-primary me-1">2</span> Personel Seçin</h5>
                        <div class="mb-3">
                            <select name="staff_id" id="bk-staff" class="form-select" required disabled
                                    data-msg-required="Lütfen bir personel seçin.">
                                <option value="">Önce hizmet seçin</option>
                            </select>
                        </div>

                        {{-- 3. Tarih & saat --}}
                        <h5 class="mb-2"><span class="badge bg-primary me-1">3</span> Tarih &amp; Saat Seçin</h5>
                        <div class="booking-datetime mb-3">
                            <div class="mb-3">
                                <label for="bk-date" class="form-label text-muted fs-13 mb-1">Tarih</label>
                                <input type="date" name="date" id="bk-date" class="form-control booking-date-input" required
                                       data-msg-required="Lütfen bir tarih seçin."
                                       min="{{ today()->toDateString() }}"
                                       max="{{ today()->addDays($business->max_advance_days)->toDateString() }}">
                            </div>
                            <div>
                                <label class="form-label text-muted fs-13 mb-1">Saat</label>
                                <div id="bk-slots" class="booking-slots is-empty">
                                    <span class="booking-slots-placeholder text-muted fs-13">Önce personel ve tarih seçin.</span>
                                </div>
                                <div id="bk-time-error" class="invalid-feedback d-none">Lütfen bir saat seçin.</div>
                                <input type="hidden" name="time" id="bk-time">
                            </div>
                        </div>

                        {{-- Dolu gunlerde bekleme listesi teklifi (JS gosterir) --}}
                        <div id="bk-waitlist" class="alert alert-warning d-none mb-3">
                            <p class="mb-2 fw-medium"><i class="ri-timer-flash-line me-1"></i>Bu gün dolu. Yer açılırsa WhatsApp ile haber verelim mi?</p>
                            <div class="row g-2">
                                <div class="col-md-5">
                                    <input type="text" id="wl-name" class="form-control form-control-sm" maxlength="120" placeholder="Ad Soyad">
                                </div>
                                <div class="col-md-4">
                                    <input type="tel" id="wl-phone" class="form-control form-control-sm" maxlength="30" placeholder="05xx xxx xx xx">
                                </div>
                                <div class="col-md-3">
                                    <button type="button" id="wl-submit" class="btn btn-warning btn-sm w-100">Listeye Katıl</button>
                                </div>
                            </div>
                        </div>

                        {{-- 4. Bilgiler --}}
                        <h5 class="mb-2"><span class="badge bg-primary me-1">4</span> Bilgileriniz</h5>
                        <div class="row g-2 mb-3">
                            <div class="col-md-6">
                                <input type="text" name="customer_name" class="form-control" required maxlength="120"
                                       data-msg-required="Ad soyad girin."
                                       placeholder="Ad Soyad *" value="{{ old('customer_name') }}">
                            </div>
                            <div class="col-md-6">
                                <input type="tel" name="customer_phone" class="form-control" required maxlength="30"
                                       data-msg-required="Telefon numarası girin."
                                       placeholder="Telefon (05xx...) *" value="{{ old('customer_phone') }}">
                            </div>
                            <div class="col-md-6">
                                <input type="email" name="customer_email" class="form-control" maxlength="190"
                                       data-msg-email="Geçerli bir e-posta adresi girin."
                                       placeholder="E-posta (isteğe bağlı)" value="{{ old('customer_email') }}">
                            </div>
                            <div class="col-md-6">
                                <input type="text" name="notes" class="form-control" maxlength="500" placeholder="Not (isteğe bağlı)" value="{{ old('notes') }}">
                            </div>
                        </div>

                        <div class="form-check mb-3">
                            <input type="checkbox" class="form-check-input" id="kvkk" name="kvkk" value="1" required
                                   data-msg-required="Devam etmek için onay kutusunu işaretleyin.">
                            <label class="form-check-label fs-13 text-muted" for="kvkk">
                                Kişisel verilerimin randevu hizmeti için işlenmesini kabul ediyorum.
                            </label>
                        </div>

                        <div class="d-grid">
                            <button type="submit" class="btn btn-primary py-2" id="bk-submit" disabled>Randevu Al</button>
                        </div>
                    </form>
                @endif
            </div>
        </div>

        <p class="text-center text-muted fs-13">
            Altyapı: @include('layouts.partials.brand-logo', ['href' => route('landing'), 'class' => 'fs-14 d-inline-flex', 'size' => 18])
        </p>
    </div>
@endsection

@section('script-bottom')
    <script>
        (function () {
            const form = document.getElementById('booking-form');
            if (!form) return;

            const serviceSelect = document.getElementById('bk-service');
            const staffSelect = document.getElementById('bk-staff');
            const dateInput = document.getElementById('bk-date');
            const slotsBox = document.getElementById('bk-slots');
            const timeInput = document.getElementById('bk-time');
            const submitBtn = document.getElementById('bk-submit');

            const waitlistBox = document.getElementById('bk-waitlist');
            const timeError = document.getElementById('bk-time-error');

            function hideTimeError() {
                timeError.classList.add('d-none');
                slotsBox.classList.remove('border', 'border-danger');
            }

            function showTimeError() {
                timeError.classList.remove('d-none');
                slotsBox.classList.add('border', 'border-danger');
                slotsBox.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
            }

            function resetSlots(message) {
                slotsBox.classList.add('is-empty');
                slotsBox.innerHTML = '<span class="booking-slots-placeholder text-muted fs-13">' + message + '</span>';
                timeInput.value = '';
                submitBtn.disabled = true;
                waitlistBox.classList.add('d-none');
                hideTimeError();
            }

            serviceSelect.addEventListener('change', function () {
                const option = serviceSelect.selectedOptions[0];
                const staff = option && option.dataset.staff ? JSON.parse(option.dataset.staff) : [];

                staffSelect.innerHTML = '';
                staffSelect.disabled = staff.length === 0;

                if (staff.length === 0) {
                    staffSelect.innerHTML = '<option value="">Bu hizmet için personel yok</option>';
                } else {
                    staffSelect.innerHTML = '<option value="">Personel seçin...</option>' +
                        staff.map(s => `<option value="${s.id}">${s.name}</option>`).join('');
                }

                resetSlots('Personel ve tarih seçin.');
            });

            async function loadSlots() {
                if (!serviceSelect.value || !staffSelect.value || !dateInput.value) return;

                resetSlots('Yükleniyor...');

                const params = new URLSearchParams({
                    service_id: serviceSelect.value,
                    staff_id: staffSelect.value,
                    date: dateInput.value,
                });

                try {
                    const response = await fetch(form.dataset.slotsUrl + '?' + params, { headers: { Accept: 'application/json' } });
                    const data = await response.json();

                    if (!data.slots || data.slots.length === 0) {
                        resetSlots('Bu tarihte uygun saat yok. Başka bir gün deneyin.');
                        waitlistBox.classList.remove('d-none');
                        return;
                    }

                    slotsBox.classList.remove('is-empty');
                    slotsBox.innerHTML = '';
                    data.slots.forEach(slot => {
                        const btn = document.createElement('button');
                        btn.type = 'button';
                        btn.className = 'btn btn-outline-primary booking-slot-btn';
                        btn.textContent = slot;
                        btn.addEventListener('click', () => {
                            slotsBox.querySelectorAll('.booking-slot-btn').forEach(b => b.classList.remove('active'));
                            btn.classList.add('active');
                            timeInput.value = slot;
                            submitBtn.disabled = false;
                            hideTimeError();
                        });
                        slotsBox.appendChild(btn);
                    });
                } catch (e) {
                    resetSlots('Saatler yüklenemedi, tekrar deneyin.');
                }
            }

            staffSelect.addEventListener('change', loadSlots);
            dateInput.addEventListener('change', loadSlots);

            form.addEventListener('submit', function (event) {
                if (! timeInput.value) {
                    event.preventDefault();
                    showTimeError();
                }
            });

            // Bekleme listesine katilim
            document.getElementById('wl-submit').addEventListener('click', async function () {
                const name = document.getElementById('wl-name').value.trim();
                const phone = document.getElementById('wl-phone').value.trim();

                if (!name || phone.length < 10) {
                    alert('Lütfen ad ve geçerli bir telefon girin.');
                    return;
                }

                const body = new FormData();
                body.append('_token', document.querySelector('meta[name="csrf-token"]').content);
                body.append('customer_name', name);
                body.append('customer_phone', phone);
                body.append('service_id', serviceSelect.value || '');
                body.append('staff_id', staffSelect.value || '');
                body.append('preferred_date', dateInput.value);

                const response = await fetch(form.dataset.waitlistUrl, { method: 'POST', body });

                if (response.ok || response.redirected) {
                    waitlistBox.innerHTML = '<p class="mb-0 fw-medium"><i class="ri-checkbox-circle-line me-1"></i>Bekleme listesine eklendiniz. Yer açılırsa WhatsApp ile haber vereceğiz.</p>';
                } else {
                    alert('Kayıt yapılamadı, lütfen tekrar deneyin.');
                }
            });
        })();
    </script>
@endsection
