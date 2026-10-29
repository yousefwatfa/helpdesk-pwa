@extends('layouts.app')
@section('content')

<div class="card shadow-sm">
    <div class="card-header">
        <h3 class="card-title">تعديل المستخدم: {{ $user->fullname }}</h3>
    </div>
    <div class="card-body">
        <form action="{{ route('admin.users.update', $user) }}" method="POST">
            @csrf
            @method('PUT')

            @if($errors->any())
                <div class="alert alert-danger mb-10" role="alert">
                    @foreach($errors->all() as $error)
                        <div>{{ $error }}</div>
                    @endforeach
                </div>
            @endif

            @php $isLocal = $user->auth_type === 'normal'; @endphp

            <div class="mb-10">
                <label class="form-label fw-bold fs-6">الاسم الكامل</label>
                @if($isLocal)
                    <input type="text" name="fullname" class="form-control form-control-solid" value="{{ old('fullname', $user->fullname) }}" required maxlength="255"/>
                @else
                    <input type="text" class="form-control form-control-solid cursor-not-allowed" value="{{ $user->fullname }}" disabled/>
                @endif
            </div>

            <div class="mb-10">
                <label class="form-label fw-bold fs-6">اسم المستخدم</label>
                <input type="text" class="form-control form-control-solid cursor-not-allowed" value="{{ $user->username }}" disabled/>
                <div class="text-muted fs-7 mt-2">{{ $isLocal ? 'حساب محلي' : 'حساب الشبكة (LDAP): الاسم والبريد يأتيان من Active Directory.' }}</div>
            </div>

            <div class="mb-10">
                <label class="form-label fw-bold fs-6">البريد الإلكتروني</label>
                @if($isLocal)
                    <input type="email" name="email" class="form-control form-control-solid" value="{{ old('email', $user->email) }}" maxlength="255"/>
                @else
                    <input type="email" class="form-control form-control-solid cursor-not-allowed" value="{{ $user->email }}" disabled/>
                @endif
            </div>

            @if($isLocal)
                <div class="mb-10">
                    <label class="form-label fw-bold fs-6">كلمة مرور جديدة</label>
                    <input type="password" name="password" class="form-control form-control-solid" minlength="8" autocomplete="new-password" placeholder="اتركها فارغة لعدم التغيير"/>
                </div>
            @endif

            <div class="mb-10">
                <label class="form-label fw-bold">الدور:</label>
              <select name="role_id" id="role_select" class="form-select form-select-solid" data-control="select2">
                @foreach($roles as $role)
                    <option value="{{ $role->id }}"
                            data-name="{{ $role->name }}"
                            {{ $user->role_id == $role->id ? 'selected' : '' }}>
                        {{ $role->label }}
                    </option>
                @endforeach
            </select>
            </div>

       {{-- لا يظهر هذا الحقل إذا كان المستخدم المطلوب تعديله هو Root --}}
            @if(!(isset($user) && $user->is_root))
            <div class="mb-10" id="department_wrapper" style="display: none;">
                <label class="form-label fw-bold">القسم المسؤول عنه:</label>
                <select name="departments[]" class="form-select form-select-solid"
                        data-control="select2"
                        data-placeholder="اختر الأقسام..."
                        data-close-on-select="false"
                        multiple="multiple">
                    @foreach($departments as $dept)
                        <option value="{{ $dept->id }}"
                            {{
                                (collect(old('departments'))->contains($dept->id)) ||
                                (isset($user) && $user->departments->contains($dept->id))
                                ? 'selected' : ''
                            }}>
                            {{ $dept->mainDepartment ? $dept->mainDepartment->name . ' - ' : '' }} {{ $dept->name }}
                        </option>
                    @endforeach
                </select>
                <div class="text-muted fs-7 mt-2">اختر الأقسام التي سيكون لهذا المستخدم صلاحية الإدارة عليها.</div>
            </div>
            @endif

        <div class="mb-10" id="branch_wrapper">
            <label class="form-label fw-bold">الفروع:</label>
            <select name="branches[]" class="form-select form-select-solid" data-control="select2" data-placeholder="بدون فرع (يرى كل الفروع)" data-close-on-select="false" multiple="multiple">
                @foreach($branches as $branch)
                    <option value="{{ $branch->id }}"
                        {{ (collect(old('branches'))->contains($branch->id)) || ($user->branches->contains($branch->id)) ? 'selected' : '' }}>
                        {{ $branch->name }}
                    </option>
                @endforeach
            </select>
            <div class="text-muted fs-7 mt-2">للموظف: فرعه. للأدمن والمشاهد: يرى فقط تذاكر هذه الفروع (فارغ = كل الفروع).</div>
        </div>

            @if(auth()->user()->is_root)
                <div class="my-10 p-4" id='admin_permissions_wrapper' style="display: none;">
                    <div class="row g-6">
                        <div class="col-md-6">
                            <div class="form-check form-switch d-flex align-items-center">
                                <input class="form-check-input"
                                    type="checkbox"
                                    role="switch"
                                    name="can_reopen_tickets"
                                    value="1"
                                    style="width: 2.5em; height: 1.25em; cursor: pointer;"
                                    id="reopenSwitch"
                                    {{ old('can_reopen_tickets', $user->can_reopen_tickets) ? 'checked' : '' }}>

                                <label class="form-check-label fw-bold text-gray-800 ms-[52px]" for="reopenSwitch" style="cursor: pointer;">
                                    سماح لهذا المستخدم بإعادة فتح التذاكر المغلقة
                                </label>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-check form-switch d-flex align-items-center">
                                <input class="form-check-input"
                                    type="checkbox"
                                    role="switch"
                                    name="can_access_finished_tickets"
                                    value="1"
                                    style="width: 2.5em; height: 1.25em; cursor: pointer;"
                                    id="finishedTicketsSwitch"
                                    {{ old('can_access_finished_tickets', $user->can_access_finished_tickets) ? 'checked' : '' }}>

                                <label class="form-check-label fw-bold text-gray-800 ms-[52px]" for="finishedTicketsSwitch" style="cursor: pointer;">
                                    سماح لهذا المستخدم بعرض صفحة المهمة المنتهية
                                </label>
                            </div>
                        </div>
                    </div>
                </div>
            @endif

            <div class="d-flex justify-content-end">
                <a href="{{ route('admin.users.index') }}" class="btn btn-light me-3">إلغاء</a>
                <button type="submit" class="btn btn-primary">
                    <span class="indicator-label">حفظ التغييرات</span>
                </button>
            </div>


        </form>
    </div>
</div>

@endsection

@push('scripts')
<script>
    $(document).ready(function() {
        const roleSelect = $('#role_select');
        const departmentWrapper = $('#department_wrapper');
        const adminPermissionsWrapper = $('#admin_permissions_wrapper');

        function toggleFeatures() {
            const selectedOption = roleSelect.find(':selected');
            const roleName = $.trim(selectedOption.data('name')).toLowerCase();


            if (roleName === 'admin') {
                departmentWrapper.stop().slideDown().css('display', 'block');


                if (adminPermissionsWrapper.length > 0) {
                    adminPermissionsWrapper.stop().slideDown().css('display', 'block');
                }
            } else if (roleName === 'viewer') {
                departmentWrapper.stop().slideDown().css('display', 'block');
                adminPermissionsWrapper.stop().slideUp();
                $('#reopenSwitch').prop('checked', false);
                $('#finishedTicketsSwitch').prop('checked', false);
            } else {
                departmentWrapper.stop().slideUp();
                adminPermissionsWrapper.stop().slideUp();
                $('#reopenSwitch').prop('checked', false);
                $('#finishedTicketsSwitch').prop('checked', false);
            }
        }

        roleSelect.on('change', function() {
            toggleFeatures();
        });

        toggleFeatures();
    });
</script>
@endpush
