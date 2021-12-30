<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Department;
use App\Models\User;

use Hash;
use Validator;
use Auth;
use DB;

class DepartmentController extends Controller
{
    public function index()
    {
        $department = Department::orderBy('id', 'asc')->latest()->get();
        if ($department) {
            return response()->json($department);
        }
    }

    public function store(Request $request)
    {
        $input = $request->all();
        $validation = Validator::make($input, [
            'name' => 'required|string|max:255',
            'description' => 'required|string|max:255',
        ]);
        if($validation->fails()){
            return response()->json(json_decode($validation->errors(), true));
        }
        $department = Department::where(['name' => $request->name])->first();
        if($department) {
            return response()->json([
                'status' => 'failure',
                'department name' => 'department name already exist please update rather'
            ]);
        }
        $depart = Department::create($input);
        return response()->json([
            'status' => 'success',
            'message' => 'department dashboard details',
            'department' => $depart,
        ]);
    }

    public function update(Request $request, $id)
    {
        $department = Department::find($id);
        $department->update($request->all());
        return response()->json($department);
    }

    public function destroy($id)
    {
        $department = Department::find($id);
        $department->delete();
        return response()->json([
           'status' => 'success',
           'department' => 'department Deleted'
        ], 200);
    }

    public function show($id)
    {
        $department = Department::find($id);
        if ($department) {
            return response()->json($department);
        }
    }

    public function addStaff(Request $request)
    {
        $input = $request->all();
        $validation = Validator::make($input, [
            'email' => 'required|string|max:255',
            'department_id' => 'required|numeric',
            'isHead'
        ]);
        if($validation->fails()){
            return response()->json(json_decode($validation->errors(), true));
        }

        $staff = User::where(['email' => $request->email])->first();

        if(!$staff) {
            return response()->json([
                'status' => 'failure',
                'staff' => 'staff email does not exist'
            ]);
        }

        $depart = DB::table('user_department')->insert([
            'department_id' => $input['department_id'],
            'isHead' => $input['isHead'],
            'user_id' => $staff->id,
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'staff added to department successfully',
            'department' => $depart,
        ]);
    }

    public function removeStaff($staffId, $departmentId)
    {
        DB::table('user_department')
            ->where([
                ['user_id', '=', $staffId],
                ['department_id', '=', $departmentId]
            ])
            ->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'staff deleted from department successfully',
        ]);
    }

    public function getStaffsInDepart()
    {
        $result = DB::table('user_department')
            ->join('users', 'users.id', '=', 'user_department.user_id')
            ->join('departments', 'departments.id', '=', 'user_department.department_id')
            ->select(
                'users.name AS userName',
                'users.email',
                'user_department.isHead',
                'departments.name AS departName',
                )
            ->get();

        return response()->json([
            'status' => 'success',
            'result' => $result,
        ]);
    }

    public function getOneStaffInDepart($staffId, $departmentId)
    {
        $result = DB::table('user_department')
            ->join('users', 'users.id', '=', 'user_department.user_id')
            ->join('departments', 'departments.id', '=', 'user_department.department_id')
            ->where([
                ['user_id', '=', $staffId],
                ['department_id', '=', $departmentId]
            ])
            ->select(
                'users.name AS userName',
                'users.email',
                'user_department.isHead',
                'departments.name AS departName',
                )
            ->get();

        return response()->json([
            'status' => 'success',
            'result' => $result,
        ]);
    }
}
