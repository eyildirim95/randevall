{{-- Tema ayar cekmecesi (acik/koyu mod secimi) --}}
<div class="offcanvas offcanvas-end border-0" tabindex="-1" id="theme-settings-offcanvas">
    <div class="d-flex align-items-center bg-primary p-3 offcanvas-header">
        <h5 class="text-white m-0">Görünüm Ayarları</h5>
        <button type="button" class="btn-close btn-close-white ms-auto" data-bs-dismiss="offcanvas" aria-label="Kapat"></button>
    </div>
    <div class="offcanvas-body p-0">
        <div data-simplebar class="h-100">
            <div class="p-3 border-bottom">
                <h5 class="mb-3 fs-16 fw-bold">Renk Modu</h5>
                <div class="row">
                    <div class="col-4">
                        <div class="form-check card-radio">
                            <input class="form-check-input" type="radio" name="data-bs-theme" id="layout-color-light" value="light">
                            <label class="form-check-label p-0 avatar-md w-100" for="layout-color-light">
                                <span class="avatar-title rounded bg-light text-dark">Açık</span>
                            </label>
                        </div>
                    </div>
                    <div class="col-4">
                        <div class="form-check card-radio">
                            <input class="form-check-input" type="radio" name="data-bs-theme" id="layout-color-dark" value="dark">
                            <label class="form-check-label p-0 avatar-md w-100" for="layout-color-dark">
                                <span class="avatar-title rounded bg-dark text-light">Koyu</span>
                            </label>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="offcanvas-footer border-top p-3 text-center">
        <button type="button" class="btn btn-light w-100" id="reset-layout">Sıfırla</button>
    </div>
</div>
