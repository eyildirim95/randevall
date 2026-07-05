@extends('layouts.vertical', ['title' => 'Takvim', 'subTitle' => $business->name])

@section('css')
    <style>
        #panel-calendar .fc-event { cursor: pointer; }
        #panel-calendar .fc-timegrid-slot { height: 2.2em; }
    </style>
@endsection

@section('content')
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex flex-wrap gap-2 align-items-center mb-3">
                        <div class="d-flex align-items-center gap-2 {{ $lockedStaffId ? 'd-none' : '' }}">
                            <label class="form-label mb-0 fw-medium" for="staff-filter">Personel:</label>
                            <select id="staff-filter" class="form-select form-select-sm" style="width:auto">
                                <option value="">Tümü</option>
                                @foreach($staffList as $staff)
                                    <option value="{{ $staff->id }}" @selected($lockedStaffId == $staff->id)>{{ $staff->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        @if($lockedStaffId)
                            <span class="badge bg-soft-info text-info">Kendi takviminizi görüntülüyorsunuz</span>
                        @endif
                        <div class="ms-auto d-flex gap-2">
                            @foreach($staffList as $staff)
                                <span class="badge" style="background-color: {{ $staff->color }}">{{ $staff->name }}</span>
                            @endforeach
                        </div>
                    </div>

                    <div id="panel-calendar"
                         data-events-url="{{ route('panel.calendar.events', $business) }}"
                         data-store-url="{{ route('panel.appointments.store', $business) }}"
                         data-move-url-template="{{ route('panel.appointments.move', [$business, '__ID__']) }}"
                         data-status-url-template="{{ route('panel.appointments.status', [$business, '__ID__']) }}"
                         data-show-url-template="{{ route('panel.appointments.show', [$business, '__ID__']) }}"
                         data-slot-interval="{{ $business->slot_interval_minutes }}"></div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('modal')
    {{-- Yeni randevu modali (takvimde bos alana tiklaninca acilir) --}}
    <div class="modal fade" id="new-appointment-modal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <form id="new-appointment-form">
                    <div class="modal-header">
                        <h5 class="modal-title">Yeni Randevu</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Kapat"></button>
                    </div>
                    <div class="modal-body">
                        <div class="alert alert-danger d-none" id="new-appointment-error"></div>

                        <div class="mb-2">
                            <label class="form-label">Tarih &amp; Saat</label>
                            <input type="text" class="form-control" id="na-datetime-display" readonly>
                            <input type="hidden" name="starts_at" id="na-starts-at">
                        </div>

                        <div class="mb-2">
                            <label class="form-label">Hizmet <span class="text-danger">*</span></label>
                            <select class="form-select" name="service_id" id="na-service" required>
                                <option value="">Seçin...</option>
                                @foreach($services as $service)
                                    <option value="{{ $service->id }}" data-duration="{{ $service->totalMinutes() }}" data-price="{{ $service->price }}">
                                        {{ $service->name }} ({{ $service->duration_minutes }} dk — {{ number_format($service->price, 0) }} {{ $business->currency }})
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="mb-2">
                            <label class="form-label">Personel <span class="text-danger">*</span></label>
                            <select class="form-select" name="staff_id" id="na-staff" required>
                                <option value="">Seçin...</option>
                                @foreach($staffList as $staff)
                                    <option value="{{ $staff->id }}">{{ $staff->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <hr>

                        <div class="mb-2">
                            <label class="form-label">Müşteri Adı <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" name="customer_name" id="na-customer-name" required maxlength="120" placeholder="Ad Soyad">
                        </div>
                        <div class="mb-2">
                            <label class="form-label">Telefon <span class="text-danger">*</span></label>
                            <input type="tel" class="form-control" name="customer_phone" id="na-customer-phone" required maxlength="30" placeholder="05xx xxx xx xx">
                        </div>
                        <div class="mb-2">
                            <label class="form-label">Not</label>
                            <textarea class="form-control" name="notes" rows="2" maxlength="1000"></textarea>
                        </div>
                        <div class="form-check mb-2">
                            <input type="checkbox" class="form-check-input" name="force" id="na-force" value="1">
                            <label class="form-check-label" for="na-force">Çalışma saati dışına izin ver</label>
                        </div>

                        <div class="row g-2">
                            <div class="col-6">
                                <label class="form-label">Tekrar</label>
                                <select class="form-select form-select-sm" name="repeat_weeks" id="na-repeat-weeks">
                                    <option value="0">Tekrarlama</option>
                                    <option value="1">Her hafta</option>
                                    <option value="2">2 haftada bir</option>
                                    <option value="4">4 haftada bir</option>
                                </select>
                            </div>
                            <div class="col-6">
                                <label class="form-label">Toplam tekrar</label>
                                <input type="number" class="form-control form-control-sm" name="repeat_count" id="na-repeat-count" min="2" max="12" value="4" disabled>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-light" data-bs-dismiss="modal">Vazgeç</button>
                        <button type="submit" class="btn btn-primary">Randevu Oluştur</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- Randevu detay modali --}}
    <div class="modal fade" id="appointment-detail-modal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="ad-title">Randevu</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Kapat"></button>
                </div>
                <div class="modal-body" id="ad-body"></div>
                <div class="modal-footer flex-wrap" id="ad-actions"></div>
            </div>
        </div>
    </div>
@endsection

@section('script-bottom')
    @vite(['resources/js/pages/panel-calendar.js'])
@endsection
