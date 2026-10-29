<?php

namespace App\Http\Controllers;

use App\Models\Branch;
use App\Services\IpTaggingService;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

/** Branches/sites (root admins only). Admins and viewers linked to branches only see tickets from users of the same branches. */
class BranchController extends Controller
{
    public function index()
    {
        $branches = Branch::withCount('users')->orderBy('name')->get();

        return view('admin.branches.index', compact('branches'));
    }

    public function store(Request $request)
    {
        Branch::create($this->validated($request));

        return back()->with('success', 'تمت إضافة الفرع.');
    }

    public function update(Request $request, Branch $branch)
    {
        $branch->update($this->validated($request, $branch));

        return back()->with('success', 'تم تحديث الفرع.');
    }

    public function destroy(Branch $branch)
    {
        $branch->delete();

        return back()->with('success', 'تم حذف الفرع.');
    }

    private function validated(Request $request, ?Branch $branch = null): array
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255', Rule::unique('branches', 'name')->ignore($branch?->id)],
            'ip_ranges' => ['nullable', 'string', 'max:1000', function ($attribute, $value, $fail) {
                foreach (array_filter(array_map('trim', explode(',', (string) $value))) as $range) {
                    if (!IpTaggingService::isValidRange($range)) {
                        $fail("نطاق الشبكة \"{$range}\" غير صحيح. مثال صحيح: 10.1.0.0/16");
                    }
                }
            }],
        ], [
            'name.required' => 'اسم الفرع مطلوب.',
            'name.unique' => 'يوجد فرع بنفس الاسم.',
        ]);

        $data['ip_ranges'] = implode(', ', array_filter(array_map('trim', explode(',', (string) ($data['ip_ranges'] ?? ''))))) ?: null;

        return $data;
    }
}
