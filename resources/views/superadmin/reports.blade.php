@extends('layouts.vertical', ['title' => 'Platform Raporları', 'subTitle' => 'Süper Admin'])

@section('content')
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header"><h4 class="card-title mb-0">Son 12 Ay Platform Metrikleri</h4></div>
                <div class="table-responsive">
                    <table class="table table-hover table-centered mb-0">
                        <thead class="bg-light bg-opacity-50">
                        <tr><th>Ay</th><th>Randevu</th><th>Mesaj</th><th>Ciro</th></tr>
                        </thead>
                        <tbody>
                        @foreach($monthlySeries->reverse() as $row)
                            <tr>
                                <td class="fw-medium">{{ $row['label'] }}</td>
                                <td>{{ $row['appointments'] }}</td>
                                <td>{{ $row['messages'] }}</td>
                                <td class="fw-semibold">{{ number_format($row['revenue'], 2, ',', '.') }} ₺</td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="card">
                <div class="card-header"><h4 class="card-title mb-0">İşletme Bazında Kullanım (Bu Ay)</h4></div>
                <div class="table-responsive">
                    <table class="table table-hover table-centered mb-0">
                        <thead class="bg-light bg-opacity-50">
                        <tr>
                            <th>İşletme</th>
                            <th>Plan</th>
                            <th>Bu Ay Randevu</th>
                            <th>Müşteri</th>
                            <th>Personel</th>
                        </tr>
                        </thead>
                        <tbody>
                        @foreach($businessUsage as $b)
                            <tr>
                                <td><a href="{{ route('admin.businesses.show', $b) }}" class="fw-medium">{{ $b->name }}</a></td>
                                <td>{{ $b->plan?->name ?? '—' }}</td>
                                <td>{{ $b->month_appointments }}</td>
                                <td>{{ $b->customers_count }}</td>
                                <td>{{ $b->staff_count }}</td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                </div>
                <div class="card-footer">{{ $businessUsage->links('pagination::bootstrap-5') }}</div>
            </div>
        </div>
    </div>
@endsection
