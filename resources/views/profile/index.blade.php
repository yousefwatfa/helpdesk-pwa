@extends('layouts.app')

@section('content')

@php
    $isLocal = $user->auth_type === 'normal';
@endphp

<div class="container py-5">
    @if($errors->any())
        <div class="alert alert-danger d-flex align-items-start gap-3 mb-6" role="alert">
            <i class="bi bi-exclamation-circle fs-3" aria-hidden="true"></i>
            <div>
                @foreach($errors->all() as $error)
                    <div>{{ $error }}</div>
                @endforeach
            </div>
        </div>
    @endif

    <div class="card shadow-sm border-0 rounded-4 overflow-hidden">

        <div class="bg-primary bg-opacity-10 px-4 d-flex align-items-center rounded-3" style="height: 100px; border-right: 5px solid var(--bs-primary);">
            <div class="d-flex justify-content-between align-items-center w-100">
                <h3 class="fw-bolder mb-0 text-primary">البيانات الشخصية</h3>
                <div class="text-primary fw-bold fs-5">
                    <i class="bi bi-geo-alt-fill me-1 text-primary"></i>
                    {{ $user->office_location ?: (session('current_branch_name') ?: 'غير محدد') }}
                </div>
            </div>
        </div>

        <div class="card-body mt-10 p-4 p-md-10">
            <div class="row g-8 mb-8">
                <div class="col-md-6">
                    <label class="form-label fw-bolder text-gray-800 fs-6">الاسم الكامل</label>
                    <div class="input-group input-group-solid">
                        <span class="input-group-text bg-light border-0"><i class="bi bi-person fs-4"></i></span>
                        <input type="text" class="form-control form-control-solid border-0" value="{{ $user->fullname }}" readonly>
                    </div>
                </div>

                <div class="col-md-6">
                    <label class="form-label fw-bolder text-gray-800 fs-6">اسم المستخدم</label>
                    <div class="input-group input-group-solid">
                        <span class="input-group-text bg-light border-0"><i class="bi bi-at fs-4"></i></span>
                        <input type="text" class="form-control form-control-solid border-0" value="{{ $user->username }}" readonly>
                        <span class="input-group-text bg-light border-0" data-bs-toggle="tooltip" title="{{ $isLocal ? 'حساب محلي' : 'حساب الشبكة (LDAP)' }}">
                            <i class="bi {{ $isLocal ? 'bi-person-badge' : 'bi-diagram-3' }} text-muted"></i>
                        </span>
                    </div>
                </div>

                <div class="col-md-6">
                    <label class="form-label fw-bolder text-gray-800 fs-6">البريد الإلكتروني</label>
                    <div class="input-group input-group-solid">
                        <span class="input-group-text bg-light border-0"><i class="bi bi-envelope fs-4"></i></span>
                        <input type="email" class="form-control form-control-solid border-0" value="{{ $user->email ?: '---' }}" readonly>
                    </div>
                </div>

                <div class="col-md-6">
                    <label class="form-label fw-bolder text-gray-800 fs-6">رقم الجوال / واتساب</label>
                    <div class="input-group input-group-solid">
                        <span class="input-group-text bg-light border-0"><i class="bi bi-whatsapp text-success fs-4"></i></span>
                        <input type="text" class="form-control form-control-solid border-0" value="{{ $user->whatsapp_number ?: '---' }}" readonly>
                    </div>
                </div>

                <div class="col-md-6">
                    <label class="form-label fw-bolder text-gray-800 fs-6">مكان العمل</label>
                    <div class="input-group input-group-solid">
                        <span class="input-group-text bg-light border-0"><i class="bi bi-building fs-4"></i></span>
                        <input type="text" class="form-control form-control-solid border-0" value="{{ $user->office_location ?: '---' }}" readonly>
                    </div>
                </div>

                <div class="col-md-6">
                    <label class="form-label fw-bolder text-gray-800 fs-6">الفروع</label>
                    <div class="input-group input-group-solid">
                        <span class="input-group-text bg-light border-0"><i class="bi bi-diagram-2 fs-4"></i></span>
                        <input type="text" class="form-control form-control-solid border-0" value="{{ $user->branches->pluck('name')->join('، ') ?: 'كل الفروع' }}" readonly>
                    </div>
                </div>

                <div class="col-12 mt-8 d-flex flex-wrap gap-3">
                    <button class="btn btn-outline btn-outline-primary fw-bolder flex-grow-1" data-bs-toggle="modal" data-bs-target="#editProfileModal">
                        <i class="bi bi-pencil-square me-2"></i> تعديل البيانات
                    </button>
                    @if($isLocal)
                        <button class="btn btn-light-primary fw-bolder flex-grow-1" data-bs-toggle="modal" data-bs-target="#changePasswordModal">
                            <i class="bi bi-key me-2"></i> تغيير كلمة المرور
                        </button>
                    @endif
                </div>
            </div>
        </div>

        {{-- Notifications on this device (web push) --}}
        <div class="card-body border-top p-6 p-lg-10">
            <h3 class="fw-bolder text-dark fs-4 mb-6">الإشعارات</h3>
            <div class="d-flex align-items-start gap-4 p-5 rounded-3 bg-light">
                <i class="bi bi-phone-vibrate fs-1 text-primary" aria-hidden="true"></i>
                <div class="flex-grow-1">
                    <div class="fw-bold fs-6 mb-1">إشعارات هذا الجهاز</div>
                    <div class="text-gray-700 fs-7 mb-4" id="push-status" aria-live="polite">جاري التحقق...</div>
                    <button type="button" class="btn btn-primary btn-sm d-none" id="push-enable-btn">
                        <i class="bi bi-bell me-1" aria-hidden="true"></i> فعّل الإشعارات
                    </button>
                    <button type="button" class="btn btn-light-danger btn-sm d-none" id="push-disable-btn">
                        <i class="bi bi-bell-slash me-1" aria-hidden="true"></i> إيقاف الإشعارات
                    </button>
                </div>
            </div>
        </div>

        <div class="card-footer bg-light-dark border-0 p-6 d-flex justify-content-between align-items-center">
            <div class="d-flex align-items-center">
                <div class="symbol symbol-35px me-3">
                    <span class="symbol-label bg-white shadow-sm">
                        <i class="bi bi-clock-history text-primary"></i>
                    </span>
                </div>
                <div class="d-flex flex-column">
                    <span class="text-gray-400 fw-bold fs-8">آخر تحديث للملف</span>
                    <span class="text-gray-800 fw-bolder fs-7">{{ $user->updated_at->format('d M Y - H:i') }}</span>
                </div>
            </div>
        </div>
    </div>
</div>

<x-update-model
    editId="editProfileModal"
    title="تعديل البيانات"
    action="{{ route('profile.update') }}"
>
    @if($isLocal)
        <div class="mb-7">
            <label class="form-label fw-bold required">الاسم الكامل</label>
            <input type="text" name="fullname" class="form-control form-control-solid" value="{{ old('fullname', $user->fullname) }}" required maxlength="255">
        </div>
        <div class="mb-7">
            <label class="form-label fw-bold">البريد الإلكتروني</label>
            <input type="email" name="email" class="form-control form-control-solid" value="{{ old('email', $user->email) }}" maxlength="255">
        </div>
    @endif
    <div class="mb-7">
        <label class="form-label fw-bold">رقم الجوال / واتساب</label>
        <input type="text" name="whatsapp_number" class="form-control form-control-solid"
               value="{{ old('whatsapp_number', $user->whatsapp_number) }}" placeholder="مثال: 0599123456"
               inputmode="tel" oninput="this.value = this.value.replace(/[^0-9+]/g, '')">
    </div>
    <div class="mb-3">
        <label class="form-label fw-bold">مكان العمل</label>
        <input type="text" name="office_location" class="form-control form-control-solid"
               value="{{ old('office_location', $user->office_location) }}" placeholder="مثال: الطابق الثاني - المحاسبة" maxlength="255">
        <div class="form-text text-muted mt-2">يُعبّأ تلقائياً في التذاكر الجديدة.</div>
    </div>
</x-update-model>

@if($isLocal)
<x-update-model
    editId="changePasswordModal"
    title="تغيير كلمة المرور"
    action="{{ route('profile.password') }}"
>
    <div class="mb-7">
        <label class="form-label fw-bold required">كلمة المرور الحالية</label>
        <input type="password" name="current_password" class="form-control form-control-solid" required autocomplete="current-password">
    </div>
    <div class="mb-7">
        <label class="form-label fw-bold required">كلمة المرور الجديدة</label>
        <input type="password" name="password" class="form-control form-control-solid" required minlength="8" autocomplete="new-password">
    </div>
    <div class="mb-3">
        <label class="form-label fw-bold required">تأكيد كلمة المرور الجديدة</label>
        <input type="password" name="password_confirmation" class="form-control form-control-solid" required minlength="8" autocomplete="new-password">
    </div>
</x-update-model>
@endif

@endsection
