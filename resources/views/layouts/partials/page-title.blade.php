<div class="row">
    <div class="col-12">
        <div class="page-title-box">
            <h4 class="mb-0 fw-semibold">{{ $title }}</h4>
            <ol class="breadcrumb mb-0">
                @if(!empty($subTitle))
                    <li class="breadcrumb-item">{{ $subTitle }}</li>
                @endif
                <li class="breadcrumb-item active">{{ $title }}</li>
            </ol>
        </div>
    </div>
</div>
