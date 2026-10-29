@extends('layouts.app')

@section('content')
<div class="row g-6">
    <div class="col-lg-7">
        <div class="card shadow-sm border-0 rounded-4">
            <div class="card-header border-0 pt-6">
                <h3 class="card-title fw-bolder fs-3">إعدادات النظام</h3>
            </div>
            <form class="card-body pt-2" method="POST" action="{{ route('admin.settings.update') }}" enctype="multipart/form-data">
                @csrf
                @method('PUT')

                @if($errors->any())
                    <div class="alert alert-danger mb-8" role="alert">
                        <ul class="mb-0 ps-0 pe-5">
                            @foreach($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <div class="mb-8">
                    <label class="form-label fw-bold required" for="name">اسم النظام</label>
                    <input type="text" id="name" name="name" class="form-control form-control-solid" value="{{ old('name', $brand->name) }}" maxlength="60" required>
                    <div class="form-text">يظهر في صفحة الدخول وعنوان المتصفح وعند تثبيت التطبيق.</div>
                </div>

                <div class="row g-6 mb-8">
                    <div class="col-md-6">
                        <label class="form-label fw-bold required" for="short_name">الاسم المختصر</label>
                        <input type="text" id="short_name" name="short_name" class="form-control form-control-solid" value="{{ old('short_name', $brand->shortName) }}" maxlength="20" required>
                        <div class="form-text">تحت أيقونة التطبيق على الهاتف (20 حرفاً كحد أقصى).</div>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-bold" for="organization">اسم المؤسسة</label>
                        <input type="text" id="organization" name="organization" class="form-control form-control-solid" value="{{ old('organization', $brand->organization) }}" maxlength="120" placeholder="مثال: شركة النور">
                    </div>
                </div>

                <div class="mb-8">
                    <label class="form-label fw-bold required" for="primary_color">اللون الرئيسي</label>
                    <div class="d-flex align-items-center gap-4">
                        <input type="color" id="primary_color" name="primary_color" class="form-control form-control-color form-control-solid w-60px h-45px p-1" value="{{ old('primary_color', $brand->primaryColor) }}">
                        <span class="text-gray-700 font-monospace" id="primary_color_value" dir="ltr">{{ old('primary_color', $brand->primaryColor) }}</span>
                    </div>
                    <div class="form-text">اختر لوناً داكناً بما يكفي ليكون النص الأبيض فوقه واضحاً.</div>
                </div>

                <div class="mb-8">
                    <label class="form-label fw-bold" for="logo">الشعار</label>
                    <input type="file" id="logo" name="logo" class="form-control form-control-solid" accept="image/png,image/jpeg,image/webp">
                    <div class="form-text">PNG أو JPG أو WEBP حتى 2 ميغابايت. يفضّل شعار مربع بخلفية شفافة أو بيضاء؛ تُصنع منه أيقونات التطبيق تلقائياً.</div>
                    @if($brand->logo)
                        <label class="d-flex align-items-center gap-3 mt-4 cursor-pointer">
                            <input class="form-check-input m-0 flex-shrink-0" type="checkbox" name="remove_logo" value="1">
                            <span class="text-gray-700">حذف الشعار والعودة للأيقونة الافتراضية</span>
                        </label>
                    @endif
                </div>

                <button type="submit" class="btn btn-primary fw-bold px-8">
                    <i class="bi bi-check2 me-1" aria-hidden="true"></i> حفظ
                </button>
            </form>
        </div>
    </div>

    <div class="col-lg-5">
        <div class="card shadow-sm border-0 rounded-4">
            <div class="card-header border-0 pt-6">
                <h3 class="card-title fw-bolder fs-4">المعاينة</h3>
            </div>
            <div class="card-body pt-2">
                <div class="d-flex align-items-center gap-4 p-5 bg-light rounded-3 mb-6">
                    <img src="{{ $brand->logoUrl() }}" alt="" class="h-60px w-60px rounded-3 bg-white object-fit-contain">
                    <div>
                        <div class="fw-bolder fs-4 text-dark">{{ $brand->name }}</div>
                        <div class="text-gray-600">{{ $brand->organization ?: '—' }}</div>
                    </div>
                </div>
                <div class="fw-bold text-gray-700 mb-3">أيقونة التطبيق على الهاتف</div>
                <div class="d-flex align-items-end gap-6">
                    <div class="text-center">
                        <img src="{{ $brand->iconUrl(192) }}" alt="" width="64" height="64" class="rounded-4 shadow-sm">
                        <div class="fs-8 mt-2 text-gray-700">{{ $brand->shortName }}</div>
                    </div>
                    <div class="text-center">
                        <img src="{{ $brand->iconUrl(512, maskable: true) }}" alt="" width="64" height="64" class="rounded-circle shadow-sm">
                        <div class="fs-8 mt-2 text-gray-500">أندرويد</div>
                    </div>
                </div>
                <div class="separator my-6"></div>
                <div class="fw-bold text-gray-700 mb-3">الأزرار</div>
                <button type="button" class="btn btn-primary btn-sm me-2">زر رئيسي</button>
                <button type="button" class="btn btn-light-primary btn-sm">زر ثانوي</button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    document.getElementById('primary_color').addEventListener('input', function () {
        document.getElementById('primary_color_value').textContent = this.value.toUpperCase();
    });
</script>
@endpush
