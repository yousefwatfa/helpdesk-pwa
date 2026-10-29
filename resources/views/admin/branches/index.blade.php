@extends('layouts.app')

@section('content')
<div class="row g-6">
    <div class="col-lg-4">
        <div class="card shadow-sm border-0 rounded-4">
            <div class="card-header border-0 pt-6">
                <h3 class="card-title fw-bolder fs-4">إضافة فرع</h3>
            </div>
            <form class="card-body pt-2" method="POST" action="{{ route('admin.branches.store') }}">
                @csrf
                @if($errors->any())
                    <div class="alert alert-danger mb-6" role="alert">
                        @foreach($errors->all() as $error)
                            <div>{{ $error }}</div>
                        @endforeach
                    </div>
                @endif
                <div class="mb-6">
                    <label class="form-label fw-bold required" for="branch_name">اسم الفرع</label>
                    <input type="text" id="branch_name" name="name" class="form-control form-control-solid" value="{{ old('name') }}" required maxlength="255" placeholder="مثال: الفرع الرئيسي">
                    @if(config('helpdesk.ldap.enabled'))
                        <div class="form-text">مع LDAP: يُربط المستخدمون تلقائياً بالفرع إذا طابق اسمه إحدى وحداتهم التنظيمية (OU).</div>
                    @endif
                </div>
                <div class="mb-6">
                    <label class="form-label fw-bold" for="branch_ranges">نطاقات الشبكة (اختياري)</label>
                    <input type="text" id="branch_ranges" name="ip_ranges" class="form-control form-control-solid font-monospace" dir="ltr" value="{{ old('ip_ranges') }}" placeholder="10.1.0.0/16, 10.2.0.0/16">
                    <div class="form-text">لتمييز التذاكر حسب الشبكة التي فُتحت منها.</div>
                </div>
                <button type="submit" class="btn btn-primary fw-bold w-100">إضافة</button>
            </form>
        </div>

        <div class="card border-0 rounded-4 bg-light-primary mt-6">
            <div class="card-body p-6 text-gray-800 lh-lg">
                <div class="fw-bolder mb-2"><i class="bi bi-info-circle me-1" aria-hidden="true"></i> كيف تعمل الفروع؟</div>
                الأدمن أو المشاهد المرتبط بفروع يرى فقط تذاكر المستخدمين من نفس الفروع. من دون فروع يرى كل التذاكر في أقسامه. اربط المستخدمين بالفروع من صفحة <a href="{{ route('admin.users.index') }}" class="fw-bold">المستخدمين</a>.
            </div>
        </div>
    </div>

    <div class="col-lg-8">
        <div class="card shadow-sm border-0 rounded-4">
            <div class="card-header border-0 pt-6">
                <h3 class="card-title fw-bolder fs-4">الفروع <span class="text-gray-500 fs-6 ms-2">({{ $branches->count() }})</span></h3>
            </div>
            <div class="card-body pt-2">
                @forelse($branches as $branch)
                    <form method="POST" action="{{ route('admin.branches.update', $branch) }}" class="row g-3 align-items-end py-4 border-bottom">
                        @csrf
                        @method('PUT')
                        <div class="col-md-4">
                            <label class="form-label fs-8 text-gray-600 mb-1" for="name_{{ $branch->id }}">الاسم</label>
                            <input type="text" id="name_{{ $branch->id }}" name="name" class="form-control form-control-sm form-control-solid" value="{{ $branch->name }}" required maxlength="255">
                        </div>
                        <div class="col-md-5">
                            <label class="form-label fs-8 text-gray-600 mb-1" for="ranges_{{ $branch->id }}">نطاقات الشبكة</label>
                            <input type="text" id="ranges_{{ $branch->id }}" name="ip_ranges" class="form-control form-control-sm form-control-solid font-monospace" dir="ltr" value="{{ $branch->ip_ranges }}" placeholder="—">
                        </div>
                        <div class="col-md-3 d-flex align-items-center gap-2">
                            <span class="badge badge-light-primary py-3 px-3 me-auto" title="عدد المستخدمين">
                                <i class="bi bi-people me-1" aria-hidden="true"></i>{{ $branch->users_count }}
                            </span>
                            <button type="submit" class="btn btn-sm btn-light-primary">حفظ</button>
                            <button type="submit" form="delete_branch_{{ $branch->id }}" class="btn btn-sm btn-light-danger"
                                    onclick="return confirm('حذف هذا الفرع؟ سيُفك ارتباط المستخدمين به.')">حذف</button>
                        </div>
                    </form>
                    <form id="delete_branch_{{ $branch->id }}" method="POST" action="{{ route('admin.branches.destroy', $branch) }}" class="d-none">
                        @csrf
                        @method('DELETE')
                    </form>
                @empty
                    <div class="text-center text-gray-600 py-10">
                        لا توجد فروع. إذا كانت مؤسستك في مكان واحد فلا تحتاج لإضافة فروع.
                    </div>
                @endforelse
            </div>
        </div>
    </div>
</div>
@endsection
