@extends('layouts.app')

@section('content')
<div class="card mb-12">
    <div class="card-header border-0 pt-6">
        <div class="card-title"><h2>إدارة الأقسام الرئيسية</h2></div>
    </div>
    <div class="card-body py-4">
        <div class="row mb-10">
            <div class="col-md-6">
                <form action="{{ route('admin.main_departments.mainDepartment_store') }}" method="POST">
                    @csrf
                    <div class="input-group">
                        <input type="text" name="name" class="form-control form-control-solid rounded-r-lg! rounded-l-none!" placeholder="اسم القسم الرئيسي الجديد" required>
                        <button class="btn btn-primary rounded-l-lg! rounded-r-none!">
                            <i class="bi bi-plus-lg p-0 me-2"></i> إضافة
                        </button>
                    </div>
                </form>
            </div>
        </div>
        <table class="table align-middle table-row-dashed fs-6 gy-5">
            <thead>
                <tr class="text-end text-gray-400 fw-bold fs-7 text-uppercase gs-0">
                    <th>ID</th><th>الاسم</th><th class="text-end">الاجراءات</th>
                </tr>
            </thead>
            <tbody>
                @foreach($mainDepartments as $main_dept)
                <tr>
                    <td>{{ $main_dept->id }}</td>
                    <td>{{ $main_dept->name }}</td>
                    <td class="text-end">
                        <form action="{{ route('admin.main_departments.mainDepartment_destroy', $main_dept->id) }}" method="POST">
                            @csrf @method('DELETE')
                            <button class="btn btn-light text-gray-400 btn-sm delete-btn">حذف</button>
                        </form>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>

<div class="card mb-12">
    <div class="card-header border-0 pt-6">
        <div class="card-title"><h2>إدارة الأقسام الفرعية</h2></div>
    </div>
    <div class="card-body py-4">
        <div class="row mb-10">

              <div class="col-md-6">
                <form action="{{ route('admin.departments.store') }}" method="POST">
                    @csrf
                    <div class="input-group">
                        <input type="text" name="name" class="form-control form-control-solid rounded-r-lg! rounded-l-none!" placeholder="اسم القسم الفرعي الجديد" required>
                        <button class="btn btn-primary rounded-l-lg! rounded-r-none!">
                            <i class="bi bi-plus-lg p-0 me-2"></i> إضافة
                        </button>
                    </div>
                </form>
            </div>
        </div>
        <table class="table align-middle table-row-dashed fs-6 gy-5">
            <thead>
                <tr class="text-end text-gray-400 fw-bold fs-7 text-uppercase gs-0">
                    <th>ID</th><th>الاسم</th><th class="text-end">حذف</th>
                </tr>
            </thead>
            <tbody>
                @foreach($departments as $department)
                <tr>
                    <td>{{ $department->id }}</td>
                    <td>{{ $department->name }}</td>
                    <td class="text-end">
                        <form action="{{ route('admin.departments.destroy', $department) }}" method="POST">
                            @csrf @method('DELETE')
                            <button class="btn btn-light text-gray-400 btn-sm delete-btn">حذف</button>
                        </form>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>

<div class='card shadow-sm mb-12'>
    <div class="card-header border-0 pt-6">
        <div class="card-title"><h3 class="fw-bolder">ربط الأقسام</h3></div>
    </div>
    <div class="card-body">
        <form action="{{ route('admin.main_departments.bindToMain') }}" method="POST" class="mb-10">
            @csrf
            <div class="row g-9 align-items-end">

                <div class="col-md-5">
                    <label class="form-label fs-6 fw-bold">القسم الفرعي</label>
                    <select name="department_id" class="form-select form-select-solid" data-control="select2" required data-placeholder='اختر القسم الفرعي...'>
                        <option value=""> </option>
                        @foreach ($departments as $department)
                            <option value="{{ $department->id }}">{{ $department->name }}</option>
                        @endforeach
                    </select>
                </div>

                 <div class="col-md-5">
                    <label class="form-label fs-6 fw-bold">القسم الرئيسي</label>
                    <select name="main_department_id" class="form-select form-select-solid" data-control="select2" required data-placeholder='اختر القسم الرئيسي...'>
                        <option value=""></option>
                        @foreach ($mainDepartments as $main)
                            <option value="{{ $main->id }}">{{ $main->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <button type="submit" class="btn btn-primary w-100">ربط الآن</button>
                </div>
            </div>
        </form>


        <div class="card-body py-4">
            {{ $dataTable->table() }}
        </div>
    </div>
</div>
@endsection

@push('scripts')
    {{ $dataTable->scripts(attributes: ['type' => 'module']) }}
@endpush
