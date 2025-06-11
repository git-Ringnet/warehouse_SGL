<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\Customer;
use App\Models\Employee;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ProjectController extends Controller
{
    /**
     * Hiển thị danh sách dự án
     */
    public function index(Request $request)
    {
        $search = $request->input('search');
        $filter = $request->input('filter');
        
        $query = Project::with('customer');
        
        // Xử lý tìm kiếm
        if ($search) {
            if ($filter) {
                // Tìm kiếm theo trường được chọn
                switch ($filter) {
                    case 'project_code':
                        $query->where('project_code', 'like', "%{$search}%");
                        break;
                    case 'project_name':
                        $query->where('project_name', 'like', "%{$search}%");
                        break;
                    case 'customer':
                        $query->whereHas('customer', function($q) use ($search) {
                            $q->where('company_name', 'like', "%{$search}%")
                              ->orWhere('name', 'like', "%{$search}%");
                        });
                        break;
                }
            } else {
                // Tìm kiếm tổng quát nếu không chọn bộ lọc
                $query->where(function ($q) use ($search) {
                    $q->where('project_code', 'like', "%{$search}%")
                      ->orWhere('project_name', 'like', "%{$search}%")
                      ->orWhereHas('customer', function($subq) use ($search) {
                          $subq->where('company_name', 'like', "%{$search}%")
                               ->orWhere('name', 'like', "%{$search}%");
                      });
                });
            }
        }
        
        $projects = $query->latest()->paginate(10);
        
        // Giữ lại tham số tìm kiếm và lọc khi phân trang
        $projects->appends([
            'search' => $search,
            'filter' => $filter
        ]);
        
        return view('projects.index', compact('projects', 'search', 'filter'));
    }

    /**
     * Hiển thị form tạo dự án mới
     */
    public function create()
    {
        $customers = Customer::all();
        $employees = Employee::where('is_active', true)->get();
        return view('projects.create', compact('customers', 'employees'));
    }

    /**
     * Lưu dự án mới vào database
     */
    public function store(Request $request)
    {
        // Validation
        $validator = Validator::make($request->all(), [
            'project_code' => 'required|string|max:255|unique:projects',
            'project_name' => 'required|string|max:255',
            'customer_id' => 'required|exists:customers,id',
            'employee_id' => 'nullable|exists:employees,id',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'warranty_period' => 'required|integer|min:1',
            'description' => 'nullable|string',
        ], [
            'project_code.required' => 'Mã dự án không được để trống',
            'project_code.unique' => 'Mã dự án đã tồn tại',
            'project_name.required' => 'Tên dự án không được để trống',
            'customer_id.required' => 'Khách hàng không được để trống',
            'customer_id.exists' => 'Khách hàng không tồn tại',
            'employee_id.exists' => 'Nhân viên phụ trách không tồn tại',
            'start_date.required' => 'Ngày bắt đầu không được để trống',
            'start_date.date' => 'Ngày bắt đầu không hợp lệ',
            'end_date.required' => 'Ngày kết thúc không được để trống',
            'end_date.date' => 'Ngày kết thúc không hợp lệ',
            'end_date.after_or_equal' => 'Ngày kết thúc phải sau hoặc bằng ngày bắt đầu',
            'warranty_period.required' => 'Thời gian bảo hành không được để trống',
            'warranty_period.integer' => 'Thời gian bảo hành phải là số nguyên',
            'warranty_period.min' => 'Thời gian bảo hành phải lớn hơn hoặc bằng 1',
        ]);

        if ($validator->fails()) {
            return redirect()
                ->back()
                ->withErrors($validator)
                ->withInput();
        }

        // Tạo dự án mới
        $project = Project::create([
            'project_code' => $request->project_code,
            'project_name' => $request->project_name,
            'customer_id' => $request->customer_id,
            'employee_id' => $request->employee_id,
            'start_date' => $request->start_date,
            'end_date' => $request->end_date,
            'warranty_period' => $request->warranty_period,
            'description' => $request->description,
        ]);

        return redirect()->route('projects.index')
            ->with('success', 'Dự án đã được thêm thành công');
    }

    /**
     * Hiển thị chi tiết dự án
     */
    public function show($id)
    {
        $project = Project::with('customer')->findOrFail($id);
        return view('projects.show', compact('project'));
    }

    /**
     * Hiển thị form chỉnh sửa dự án
     */
    public function edit($id)
    {
        $project = Project::findOrFail($id);
        $customers = Customer::all();
        $employees = Employee::where('is_active', true)->get();
        return view('projects.edit', compact('project', 'customers', 'employees'));
    }

    /**
     * Cập nhật dự án trong database
     */
    public function update(Request $request, $id)
    {
        // Validation
        $validator = Validator::make($request->all(), [
            'project_code' => 'required|string|max:255|unique:projects,project_code,'.$id,
            'project_name' => 'required|string|max:255',
            'customer_id' => 'required|exists:customers,id',
            'employee_id' => 'nullable|exists:employees,id',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'warranty_period' => 'required|integer|min:1',
            'description' => 'nullable|string',
        ], [
            'project_code.required' => 'Mã dự án không được để trống',
            'project_code.unique' => 'Mã dự án đã tồn tại',
            'project_name.required' => 'Tên dự án không được để trống',
            'customer_id.required' => 'Khách hàng không được để trống',
            'customer_id.exists' => 'Khách hàng không tồn tại',
            'employee_id.exists' => 'Nhân viên phụ trách không tồn tại',
            'start_date.required' => 'Ngày bắt đầu không được để trống',
            'start_date.date' => 'Ngày bắt đầu không hợp lệ',
            'end_date.required' => 'Ngày kết thúc không được để trống',
            'end_date.date' => 'Ngày kết thúc không hợp lệ',
            'end_date.after_or_equal' => 'Ngày kết thúc phải sau hoặc bằng ngày bắt đầu',
            'warranty_period.required' => 'Thời gian bảo hành không được để trống',
            'warranty_period.integer' => 'Thời gian bảo hành phải là số nguyên',
            'warranty_period.min' => 'Thời gian bảo hành phải lớn hơn hoặc bằng 1',
        ]);

        if ($validator->fails()) {
            return redirect()
                ->back()
                ->withErrors($validator)
                ->withInput();
        }

        // Cập nhật dự án
        $project = Project::findOrFail($id);
        $project->update([
            'project_code' => $request->project_code,
            'project_name' => $request->project_name,
            'customer_id' => $request->customer_id,
            'employee_id' => $request->employee_id,
            'start_date' => $request->start_date,
            'end_date' => $request->end_date,
            'warranty_period' => $request->warranty_period,
            'description' => $request->description,
        ]);

        return redirect()->route('projects.show', $id)
            ->with('success', 'Thông tin dự án đã được cập nhật thành công');
    }

    /**
     * Xóa dự án khỏi database
     */
    public function destroy($id)
    {
        $project = Project::findOrFail($id);
        $project->delete();
        
        return redirect()->route('projects.index')
            ->with('success', 'Dự án đã được xóa thành công');
    }
} 