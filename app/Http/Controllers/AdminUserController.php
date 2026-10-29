<?php

namespace App\Http\Controllers;

use App\DataTables\UsersDataTable;
use App\Models\Role;
use App\Models\User;
use App\Models\Department;
use App\Models\Branch;

use Illuminate\Http\Request;

class AdminUserController extends Controller
{
    public function create()
    {
        $roles = Role::all();
        $departments = Department::all();
        $branches = Branch::orderBy('name')->get();
        return view('admin.users.create', compact('roles', 'departments', 'branches'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'username' => 'required|max:255|unique:users',
            'fullname' => 'required|max:255',
            'email' => 'nullable|email|unique:users',
            'role_id' => 'required|exists:roles,id',
            'auth_type' => config('helpdesk.ldap.enabled') ? 'required|in:ldap,normal' : 'required|in:normal',
            'password' => 'nullable|min:8|required_if:auth_type,normal',
            'departments' => 'nullable|array',
            'departments.*' => 'exists:departments,id',
            'branches' => 'nullable|array',
            'branches.*' => 'exists:branches,id',
        ], [
            'username.unique' => 'اسم المستخدم مستخدم من قبل.',
            'password.required_if' => 'كلمة المرور مطلوبة للحساب المحلي.',
            'password.min' => 'كلمة المرور يجب أن تكون 8 أحرف على الأقل.',
        ]);

        $data = $request->only(['username', 'fullname', 'email', 'role_id', 'auth_type']);


        if ($request->auth_type === 'normal') {
            $data['password'] = \Illuminate\Support\Facades\Hash::make($request->password);
        } else {
            $data['password'] = null;
        }

        $user = User::create($data);

        $role = Role::find($request->role_id);


        if (auth()->user()->is_root) {
            $user->can_reopen_tickets = (strtolower($role->name) === 'admin' && $request->has('can_reopen_tickets')) ? 1 : 0;
            $user->can_access_finished_tickets = (strtolower($role->name) === 'admin' && $request->has('can_access_finished_tickets')) ? 1 : 0;
            $user->save();
        }

        if (strtolower($role->name) === 'admin' || strtolower($role->name) === 'viewer') {
            $user->departments()->sync($request->input('departments', []));
        }

        $user->branches()->sync($request->input('branches', []));

        return redirect()->route('admin.users.index')->with('success', 'تمت إضافة المستخدم بنجاح');
    }

    public function index(UsersDataTable $dataTable)
    {
        return $dataTable->render('admin.users.index');
    }

    public function edit(User $user)
    {
        $roles = Role::all();
        $departments = Department::all();
        $branches = Branch::all();
        return view('admin.users.edit', compact('user', 'roles','departments','branches'));
    }

public function update(Request $request, User $user)
{
    $request->validate([
        'role_id' => 'required|exists:roles,id',
        'departments' => 'nullable|array',
        'departments.*' => 'exists:departments,id',
        'can_reopen_tickets' => 'nullable|boolean',
        'can_access_finished_tickets' => 'nullable|boolean',
        'branches' => 'nullable|array',
        'branches.*' => 'exists:branches,id',
        'fullname' => 'sometimes|required|string|max:255',
        'email' => 'nullable|email|max:255|unique:users,email,' . $user->id,
        'password' => 'nullable|min:8',
    ], [
        'password.min' => 'كلمة المرور يجب أن تكون 8 أحرف على الأقل.',
        'email.unique' => 'البريد الإلكتروني مسجل لمستخدم آخر.',
    ]);

    // name, email and password only for local accounts (LDAP users get them from AD)
    $hasAccountChanged = false;
    if ($user->auth_type === 'normal') {
        $user->fill($request->only(array_filter(['fullname', 'email'], fn ($field) => $request->has($field))));
        if ($request->filled('password')) {
            $user->password = \Illuminate\Support\Facades\Hash::make($request->password);
        }
        $hasAccountChanged = $user->isDirty();
        $user->save();
    }

    if ($user->is_root) {
        $newDeptIds = []; // الروت لا يحتاج لربط أقسام محددة
    } else {
        $newDeptIds = $request->input('departments', []);
    }

    $currentDeptIds = $user->departments()->pluck('departments.id')->toArray();
    sort($currentDeptIds);
    sort($newDeptIds);

    $currentBranchIds = $user->branches()->pluck('branches.id')->toArray();
    $newBranchIds = $request->input('branches', []);
    sort($currentBranchIds);
    sort($newBranchIds);

    $newCanReopenValue = $request->has('can_reopen_tickets') ? 1 : 0;
    $newCanAccessFinishedTicketsValue = $request->has('can_access_finished_tickets') ? 1 : 0;

    $hasRoleChanged = $user->role_id != $request->role_id;
    $hasDeptsChanged = !$user->is_root && ($currentDeptIds != $newDeptIds);
    $hasBranchesChanged = $currentBranchIds != $newBranchIds;
    $hasPermissionChanged = auth()->user()->is_root && (
        $user->can_reopen_tickets != $newCanReopenValue ||
        $user->can_access_finished_tickets != $newCanAccessFinishedTicketsValue
    );

    if (!$hasAccountChanged && !$hasRoleChanged && !$hasDeptsChanged && !$hasBranchesChanged && !$hasPermissionChanged) {
        return back()->with('info', 'هذه هي المعلومات المسجلة حالياً للمستخدم');
    }

    $user->update(['role_id' => $request->role_id]);

    $role = Role::find($request->role_id);

    // إدارة صلاحية إعادة فتح التذاكر
    if (auth()->user()->is_root) {
        if (strtolower($role->name) === 'admin') {
            $user->can_reopen_tickets = $newCanReopenValue;
            $user->can_access_finished_tickets = $newCanAccessFinishedTicketsValue;
        } else {
            $user->can_reopen_tickets = 0;
            $user->can_access_finished_tickets = 0;
        }
        $user->save();
    }

    // 2. تحديث الأقسام بناءً على الرتبة ونوع المستخدم
    if ($user->is_root) {
        // الروت لا يربط بأقسام محددة (صلاحية شاملة)
        $user->departments()->detach();
    } elseif (strtolower($role->name) === 'admin' || strtolower($role->name) === 'viewer') {
        $user->departments()->sync($request->input('departments', []));
    } else {
        $user->departments()->detach();
    }

    $user->branches()->sync($request->input('branches', []));

    return redirect()->route('admin.users.index')->with('success', 'تم تحديث البيانات بنجاح');
}





}
