{{-- Şirket bilgisi alanı: doluysa gösterir, boşsa doldurulacak uyarısı --}}
@php($val = config('legal.'.$key))
@if($val)<strong>{{ $val }}</strong>@else<span class="placeholder">[{{ $label ?? strtoupper($key) }} — config/legal.php içinde doldurun]</span>@endif
