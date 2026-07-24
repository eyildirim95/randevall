import { Calendar } from '@fullcalendar/core';
import dayGridPlugin from '@fullcalendar/daygrid';
import timeGridPlugin from '@fullcalendar/timegrid';
import listPlugin from '@fullcalendar/list';
import interactionPlugin from '@fullcalendar/interaction';
import trLocale from '@fullcalendar/core/locales/tr';

const el = document.getElementById('panel-calendar');

if (el) {
    const csrf = document.querySelector('meta[name="csrf-token"]').content;
    const staffFilter = document.getElementById('staff-filter');
    const slotInterval = parseInt(el.dataset.slotInterval || '15', 10);

    const newModalEl = document.getElementById('new-appointment-modal');
    const detailModalEl = document.getElementById('appointment-detail-modal');
    const newModal = bootstrap.Modal.getOrCreateInstance(newModalEl);
    const detailModal = bootstrap.Modal.getOrCreateInstance(detailModalEl);

    const newAppointmentForm = document.getElementById('new-appointment-form');
    const customerIdInput = document.getElementById('na-customer-id');
    const customerNameInput = document.getElementById('na-customer-name');
    const customerPhoneInput = document.getElementById('na-customer-phone');
    const customerMatchHint = document.getElementById('na-customer-match');
    const newAppointmentBtn = document.getElementById('btn-new-appointment');

    let lastModalOpenAt = 0;
    let preservedCustomer = null;

    const urlFor = (template, id) => template.replace('__ID__', id);

    async function jsonFetch(url, options = {}) {
        const response = await fetch(url, {
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrf,
                'Accept': 'application/json',
                ...options.headers,
            },
            ...options,
        });

        const data = await response.json().catch(() => ({}));

        if (!response.ok) {
            const message = data.message || Object.values(data.errors || {}).flat().join(' ') || 'İşlem başarısız.';
            throw new Error(message);
        }

        return data;
    }

    function pad(n) { return String(n).padStart(2, '0'); }

    function toLocalIso(date) {
        return `${date.getFullYear()}-${pad(date.getMonth() + 1)}-${pad(date.getDate())} ${pad(date.getHours())}:${pad(date.getMinutes())}:00`;
    }

    function isSameDay(a, b) {
        return a.getFullYear() === b.getFullYear()
            && a.getMonth() === b.getMonth()
            && a.getDate() === b.getDate();
    }

    function roundToNextSlot(date) {
        const rounded = new Date(date);
        rounded.setSeconds(0, 0);
        const minutes = rounded.getMinutes();
        const remainder = minutes % slotInterval;
        if (remainder !== 0) {
            rounded.setMinutes(minutes + (slotInterval - remainder));
        }
        if (rounded <= date) {
            rounded.setMinutes(rounded.getMinutes() + slotInterval);
        }
        return rounded;
    }

    function snapToSlot(date) {
        const snapped = new Date(date);
        snapped.setSeconds(0, 0);
        const minutes = snapped.getMinutes();
        snapped.setMinutes(minutes - (minutes % slotInterval));
        return snapped;
    }

    /** Ay/gun gorunumunde saat yoksa varsayilan acilis saati; hafta/gun gorunumunde tiklanan slot. */
    function resolveClickedDate(date, viewType) {
        const clicked = new Date(date);
        const isMonthLike = viewType.includes('dayGrid') || viewType.includes('list');

        if (isMonthLike || (clicked.getHours() === 0 && clicked.getMinutes() === 0 && clicked.getSeconds() === 0)) {
            clicked.setHours(9, 0, 0, 0);
            const now = new Date();
            if (isSameDay(clicked, now) && clicked < now) {
                return roundToNextSlot(now);
            }
            return clicked;
        }

        return snapToSlot(clicked);
    }

    function setCustomerPrefill(customer) {
        preservedCustomer = customer;
        customerIdInput.value = customer.id;
        customerNameInput.value = customer.name;
        customerPhoneInput.value = customer.phone;
        customerNameInput.readOnly = true;
        customerPhoneInput.readOnly = true;
        customerMatchHint.textContent = `Kayıtlı müşteri: ${customer.name}`;
        customerMatchHint.classList.remove('d-none', 'text-danger', 'text-muted');
        customerMatchHint.classList.add('text-success');
    }

    function clearCustomerPrefill(resetFields = true) {
        preservedCustomer = null;
        customerIdInput.value = '';
        customerNameInput.readOnly = false;
        customerPhoneInput.readOnly = false;
        customerMatchHint.textContent = '';
        customerMatchHint.classList.add('d-none');
        customerMatchHint.classList.remove('text-success', 'text-danger', 'text-muted');
        if (resetFields) {
            customerNameInput.value = '';
            customerPhoneInput.value = '';
        }
    }

    function resetNewAppointmentForm() {
        newAppointmentForm.reset();
        document.getElementById('na-repeat-count').disabled = true;
        document.getElementById('new-appointment-error').classList.add('d-none');
    }

    async function lookupCustomerByPhone(phone) {
        if (!phone || phone.length < 10 || customerIdInput.value) return;

        const params = new URLSearchParams({ phone });
        const response = await fetch(`${el.dataset.customerLookupUrl}?${params}`, {
            headers: { Accept: 'application/json' },
        });

        if (!response.ok) return;

        const data = await response.json();

        if (data.found) {
            setCustomerPrefill(data.customer);
        } else {
            customerIdInput.value = '';
            customerMatchHint.textContent = 'Yeni müşteri olarak kaydedilecek.';
            customerMatchHint.classList.remove('d-none', 'text-success');
            customerMatchHint.classList.add('text-muted');
        }
    }

    function openNewModal(date, viewType = 'timeGridWeek', { keepCustomer = false } = {}) {
        const resolved = resolveClickedDate(date, viewType);
        const minAllowed = new Date(Date.now() - 24 * 3600 * 1000);

        if (resolved < minAllowed) {
            return;
        }

        const now = Date.now();
        if (now - lastModalOpenAt < 250) {
            return;
        }
        lastModalOpenAt = now;

        resetNewAppointmentForm();

        if (keepCustomer && preservedCustomer) {
            setCustomerPrefill(preservedCustomer);
        } else if (!keepCustomer) {
            clearCustomerPrefill(true);
        }

        document.getElementById('na-starts-at').value = toLocalIso(resolved);
        document.getElementById('na-datetime-display').value = resolved.toLocaleString('tr-TR', {
            dateStyle: 'full',
            timeStyle: 'short',
        });

        if (staffFilter?.value) {
            document.getElementById('na-staff').value = staffFilter.value;
        }

        newModal.show();

        window.setTimeout(() => {
            document.getElementById('na-service')?.focus();
        }, 300);
    }

    function handleCalendarSlotClick(date, viewType) {
        openNewModal(date, viewType, { keepCustomer: !!preservedCustomer });
    }

    const calendar = new Calendar(el, {
        plugins: [dayGridPlugin, timeGridPlugin, listPlugin, interactionPlugin],
        locale: trLocale,
        initialView: window.matchMedia('(max-width: 767.98px)').matches ? 'timeGridDay' : 'timeGridWeek',
        headerToolbar: {
            left: 'prev,next today',
            center: 'title',
            right: 'dayGridMonth,timeGridWeek,timeGridDay,listWeek',
        },
        height: 'auto',
        nowIndicator: true,
        slotMinTime: '07:00:00',
        slotMaxTime: '23:00:00',
        slotDuration: { minutes: slotInterval },
        snapDuration: { minutes: slotInterval },
        selectable: true,
        selectMirror: true,
        unselectAuto: true,
        selectMinDistance: 4,
        longPressDelay: 150,
        editable: true,
        eventResizableFromStart: false,
        dayMaxEvents: true,

        events: (info, success, failure) => {
            const params = new URLSearchParams({ start: info.startStr, end: info.endStr });
            if (staffFilter?.value) params.set('staff_id', staffFilter.value);

            fetch(`${el.dataset.eventsUrl}?${params}`, { headers: { Accept: 'application/json' } })
                .then((r) => r.json())
                .then(success)
                .catch(failure);
        },

        // Bos slota tikla / dokun → randevu modali (tiklanan saat)
        dateClick: (info) => {
            if (info.dayEl?.classList.contains('fc-day-past')) {
                return;
            }
            handleCalendarSlotClick(info.date, info.view.type);
        },

        // Surukleyerek secim (mobil + masaustu yedek)
        select: (info) => {
            calendar.unselect();
            if (info.start < new Date(Date.now() - 24 * 3600 * 1000)) {
                return;
            }
            handleCalendarSlotClick(info.start, info.view.type);
        },

        eventDrop: (info) => moveEvent(info),
        eventResize: (info) => moveEvent(info),

        eventClick: (info) => {
            info.jsEvent.preventDefault();
            if (info.event.extendedProps.type !== 'appointment') return;
            openDetailModal(info.event);
        },
    });

    calendar.render();
    staffFilter?.addEventListener('change', () => calendar.refetchEvents());

    customerPhoneInput.addEventListener('blur', () => {
        if (!customerPhoneInput.readOnly) {
            lookupCustomerByPhone(customerPhoneInput.value.trim());
        }
    });

    customerPhoneInput.addEventListener('input', () => {
        if (customerIdInput.value) {
            clearCustomerPrefill(true);
        }
    });

    newAppointmentBtn?.addEventListener('click', () => {
        openNewModal(roundToNextSlot(new Date()), calendar.view.type, { keepCustomer: !!preservedCustomer });
    });

    if (el.dataset.prefillCustomer) {
        try {
            const customer = JSON.parse(el.dataset.prefillCustomer);
            setCustomerPrefill(customer);
            openNewModal(roundToNextSlot(new Date()), calendar.view.type, { keepCustomer: true });
        } catch (_) {
            // ignore invalid json
        }
    }

    // PWA alt menu ve diger scriptler icin global API
    window.openPanelAppointmentModal = (date = new Date(), options = {}) => {
        openNewModal(date, calendar.view.type, options);
    };

    const repeatWeeks = document.getElementById('na-repeat-weeks');
    const repeatCount = document.getElementById('na-repeat-count');

    repeatWeeks.addEventListener('change', () => {
        repeatCount.disabled = repeatWeeks.value === '0';
    });

    newAppointmentForm.addEventListener('submit', async (e) => {
        e.preventDefault();

        const form = e.target;
        const payload = Object.fromEntries(new FormData(form).entries());
        payload.force = form.querySelector('#na-force').checked;

        if (repeatWeeks.value === '0') {
            delete payload.repeat_weeks;
            delete payload.repeat_count;
        }

        const errorBox = document.getElementById('new-appointment-error');

        try {
            const data = await jsonFetch(el.dataset.storeUrl, { method: 'POST', body: JSON.stringify(payload) });
            newModal.hide();
            resetNewAppointmentForm();
            clearCustomerPrefill(true);
            repeatCount.disabled = true;
            calendar.refetchEvents();

            if (data.message && data.message.includes('atlandı')) {
                alert(data.message);
            }
        } catch (err) {
            errorBox.textContent = err.message;
            errorBox.classList.remove('d-none');
        }
    });

    async function moveEvent(info) {
        const id = info.event.extendedProps.appointmentId;

        try {
            await jsonFetch(urlFor(el.dataset.moveUrlTemplate, id), {
                method: 'PATCH',
                body: JSON.stringify({
                    starts_at: toLocalIso(info.event.start),
                    ends_at: info.event.end ? toLocalIso(info.event.end) : null,
                }),
            });
        } catch (err) {
            info.revert();
            alert(err.message);
        }
    }

    function escapeHtml(value) {
        const div = document.createElement('div');
        div.textContent = value ?? '';
        return div.innerHTML;
    }

    function openDetailModal(event) {
        const p = event.extendedProps;
        const id = p.appointmentId;

        document.getElementById('ad-title').textContent = p.customer;

        document.getElementById('ad-body').innerHTML = `
            <table class="table table-sm table-borderless mb-0">
                <tr><th class="text-muted" style="width:110px">Durum</th><td><span class="badge bg-primary">${escapeHtml(p.statusLabel)}</span></td></tr>
                <tr><th class="text-muted">Telefon</th><td><a href="tel:${escapeHtml(p.phone)}">${escapeHtml(p.phone)}</a></td></tr>
                <tr><th class="text-muted">Hizmet</th><td>${escapeHtml(p.service || '—')}</td></tr>
                <tr><th class="text-muted">Personel</th><td>${escapeHtml(p.staff || '—')}</td></tr>
                <tr><th class="text-muted">Saat</th><td>${event.start.toLocaleString('tr-TR', { dateStyle: 'medium', timeStyle: 'short' })}</td></tr>
                <tr><th class="text-muted">Ücret</th><td>${p.price} </td></tr>
                ${p.notes ? `<tr><th class="text-muted">Not</th><td>${escapeHtml(p.notes)}</td></tr>` : ''}
            </table>`;

        const actions = document.getElementById('ad-actions');
        actions.innerHTML = '';

        const buttons = [];

        if (p.status === 'pending') buttons.push({ label: 'Onayla', cls: 'btn-primary', status: 'confirmed' });
        if (p.status === 'pending' || p.status === 'confirmed') {
            buttons.push({ label: 'Tamamlandı', cls: 'btn-success', status: 'completed' });
            buttons.push({ label: 'Gelmedi', cls: 'btn-secondary', status: 'no_show' });
            buttons.push({ label: 'İptal Et', cls: 'btn-danger', status: 'cancelled' });
        }

        buttons.forEach(({ label, cls, status }) => {
            const btn = document.createElement('button');
            btn.className = `btn btn-sm ${cls}`;
            btn.textContent = label;
            btn.addEventListener('click', async () => {
                if (status === 'cancelled' && !confirm('Randevu iptal edilsin mi? Müşteriye bildirim gönderilir.')) return;

                try {
                    await jsonFetch(urlFor(el.dataset.statusUrlTemplate, id), {
                        method: 'PATCH',
                        body: JSON.stringify({ status }),
                    });
                    detailModal.hide();
                    calendar.refetchEvents();
                } catch (err) {
                    alert(err.message);
                }
            });
            actions.appendChild(btn);
        });

        const detailLink = document.createElement('a');
        detailLink.className = 'btn btn-sm btn-light ms-auto';
        detailLink.href = urlFor(el.dataset.showUrlTemplate, id);
        detailLink.textContent = 'Detay Sayfası';
        actions.appendChild(detailLink);

        detailModal.show();
    }
}
