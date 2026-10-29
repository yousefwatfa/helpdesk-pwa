<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use \App\Models\Department;
use \App\Models\MainDepartment;
use App\DataTables\DepartmentBindingDataTable;

class DepartmentController extends Controller
{
    public function index(DepartmentBindingDataTable $dataTable) {
        $departments = Department::all();
          $mainDepartments = MainDepartment::all();
        return $dataTable->render('admin.departments.index',compact('departments','mainDepartments'));
    }


    public function store(Request $request) {

        $request->validate([
            'name' => 'required',
            'main_department_id' => 'nullable|exists:departments,id'
        ]);

        Department::create([
            'name' => $request->name,
            'main_department_id' => $request->main_department_id
        ]);

         return back()->with('success','تم اضافة قسم فرعي بنجاح');

    }

    public function destroy(Department $department) {
        $department->delete();
        return back()->with('success','تم حذف قسم فرعي بنجاح');
    }


    public function bindToMain(Request $request)
    {

        $request->validate([
            'main_department_id' => 'required|exists:main_departments,id',
            'department_id'      => 'required|exists:departments,id',
        ]);

        $department = Department::findOrFail($request->department_id);


    if ($department->main_department_id == $request->main_department_id) {
        return back()->with('info', 'هذا القسم الفرعي مرتبط بالفعل بهذا القسم الرئيسي مسبقاً');
    }
        $department->update([
            'main_department_id' => $request->main_department_id
        ]);

        return back()->with('success', 'تم ربط القسم الفرعي بالقسم الرئيسي بنجاح');
    }

    public function unbindDepartment($id)
    {
        $department = Department::findOrFail($id);
        $department->update([
            'main_department_id' => null
        ]);
        return back();
    }



    public function mainDepartment_store(Request $request) {
        $request->validate(['name' => 'required']);
        MainDepartment::create($request->only('name'));
        return back()->with('success','تم اضافة قسم رئيسي بنجاح');
    }

  public function mainDepartment_destroy(MainDepartment $main_department)
    {
        if ($main_department->departments()->exists()) {
            return back()->with('info', 'لا يمكن حذف القسم! يرجى حذف أو نقل الأقسام الفرعية التابعة له أولاً.');
        }
        $main_department->delete();
        return back()->with('success','تم حذف قسم رئيسي بنجاح');
    }
}

