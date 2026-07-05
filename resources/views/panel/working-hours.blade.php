@extends('layouts.vertical', ['title' => 'Çalışma Saatleri', 'subTitle' => $business->name])

@section('content')
    <div class="row">
        <div class="col-xl-8">
            <div class="card">
                <div class="card-header d-flex flex-wrap justify-content-between align-items-center gap-2">
                    <h4 class="card-title mb-0">
                        {{ $staffId ? $staffList->firstWhere('id', $staffId)?->name.' — Özel Saatler' : 'İşletme Geneli Saatler' }}
                    </h4>
                    <form method="GET" class="d-flex gap-2 align-items-center">
                        <label class="form-label mb-0">Personel:</label>
                        <select name="staff_id" class="form-select form-select-sm" onchange="this.form.submit()">
                            <option value="">İşletme Geneli</option>
                            @foreach($staffList as $staff)
                                <option value="{{ $staff->id }}" @selected($staffId == $staff->id)>{{ $staff->name }}</option>
                            @endforeach
                        </select>
                    </form>
                </div>
                <div class="card-body">
                    @if($staffId)
                        <div class="alert alert-info py-2">
                            Personel için özel saat girilmezse işletme geneli saatler uygulanır.
                        </div>
                    @endif

                    <form method="POST" action="{{ route('panel.working-hours.update', $business) }}">
                        @csrf
                        @if($staffId)
                            <input type="hidden" name="staff_id" value="{{ $staffId }}">
                        @endif

                        <div class="table-responsive">
                            <table class="table table-centered align-middle">
                                <thead class="bg-light bg-opacity-50">
                                <tr>
                                    <th>Gün</th>
                                    <th>Kapalı</th>
                                    <th>Açılış</th>
                                    <th>Kapanış</th>
                                    <th>Mola Başlangıç</th>
                                    <th>Mola Bitiş</th>
                                </tr>
                                </thead>
                                <tbody>
                                @foreach($dayNames as $dow => $dayName)
                                    @php $hour = $hours->get($dow); @endphp
                                    <tr>
                                        <td class="fw-medium">{{ $dayName }}</td>
                                        <td>
                                            <div class="form-check form-switch">
                                                <input type="hidden" name="days[{{ $dow }}][is_closed]" value="0">
                                                <input type="checkbox" class="form-check-input" name="days[{{ $dow }}][is_closed]" value="1" @checked($hour?->is_closed)>
                                            </div>
                                        </td>
                                        <td><input type="time" class="form-control form-control-sm" name="days[{{ $dow }}][start_time]" value="{{ $hour?->start_time ? substr($hour->start_time, 0, 5) : '09:00' }}"></td>
                                        <td><input type="time" class="form-control form-control-sm" name="days[{{ $dow }}][end_time]" value="{{ $hour?->end_time ? substr($hour->end_time, 0, 5) : '19:00' }}"></td>
                                        <td><input type="time" class="form-control form-control-sm" name="days[{{ $dow }}][break_start]" value="{{ $hour?->break_start ? substr($hour->break_start, 0, 5) : '' }}"></td>
                                        <td><input type="time" class="form-control form-control-sm" name="days[{{ $dow }}][break_end]" value="{{ $hour?->break_end ? substr($hour->break_end, 0, 5) : '' }}"></td>
                                    </tr>
                                @endforeach
                                </tbody>
                            </table>
                        </div>

                        <button class="btn btn-primary">Kaydet</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection
