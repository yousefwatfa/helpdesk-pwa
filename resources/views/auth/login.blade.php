@extends('layouts.auth')
@section('content')
<form class="form w-100" novalidate="novalidate" method="POST" action="{{ route('login') }}">
    @csrf
    <div class="text-center mb-10">
        <h1 class="text-dark mb-3">تسجيل الدخول</h1>
    </div>

    <div class="fv-row mb-10">
        <label class="form-label fs-6 fw-bolder text-dark" for="username">اسم المستخدم</label>
        <input class="form-control form-control-lg form-control-solid" type="text" id="username" name="username" value="{{ old('username') }}" autocomplete="username" required autofocus />
        @error('username') <span class="text-danger">{{ $message }}</span> @enderror
    </div>

    <div class="fv-row mb-8">
        <div class="d-flex flex-stack mb-2">
            <label class="form-label fw-bolder text-dark fs-6 mb-0" for="password">كلمة المرور</label>
        </div>
        <input class="form-control form-control-lg form-control-solid" type="password" id="password" name="password" autocomplete="current-password" required />
    </div>

    <label class="d-flex align-items-center gap-3 mb-10 cursor-pointer">
        <input class="form-check-input m-0 flex-shrink-0" type="checkbox" name="remember" value="1" />
        <span class="fw-bold text-gray-700">تذكرني على هذا الجهاز</span>
    </label>

    <div class="text-center">
        <button type="submit" class="btn btn-lg btn-primary w-100 mb-5">
            <span class="indicator-label">دخول</span>
        </button>
    </div>
</form>

@if(config('helpdesk.ldap.enabled'))
    <div class="mt-4 p-4 bg-light-primary rounded-3 d-flex align-items-start gap-3">
        <i class="bi bi-info-circle-fill text-primary fs-3" aria-hidden="true"></i>
        <p class="mb-0 text-gray-800 lh-base">يمكنك الدخول بحساب الشبكة الخاص بك (نفس اسم المستخدم وكلمة المرور لجهاز العمل).</p>
    </div>
@endif

@endsection
