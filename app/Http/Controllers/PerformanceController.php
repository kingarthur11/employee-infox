<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Performance;

use Hash;
use Validator;
use Auth;
use DB;

class PerformanceController extends Controller
{
    public function store(Request $request)
    {
        $input = $request->all();
        $validation = Validator::make($input, [
            'user_id' => 'required|numeric',
            'performanceLevel' => 'required|string|max:255',
            'dateOfPerformance' => 'required|date',
        ]);
        if($validation->fails()){
            return response()->json(json_decode($validation->errors(), true));
        }
        $performance = Performance::create($input);
        return response()->json([
            'status' => 'success',
            'performance' => $performance,
        ]);
    }

    public function searchPerfomByYear(Request $request)
    {
        $year = $request->input('year');

        $performance = DB::table('users')
                        ->join('performances', 'performances.user_id', '=', 'users.id')
                        ->whereYear('performances.dateOfPerformance', '=', $year)
                        ->select(
                            'performances.performanceLevel',
                            'users.name',
                            'users.email',
                            )
                        ->get();

        return response()->json([
            'status' => 'success',
            'message' => 'user dashboard details',
            'performance' => $performance
        ]);
    }

    public function searchPerfomByMonth(Request $request)
    {
        $month = $request->input('month');

        $performance = DB::table('users')
                        ->join('performances', 'performances.user_id', '=', 'users.id')
                        ->whereMonth('performances.dateOfPerformance', '=', $month)
                        ->select(
                            'performances.performanceLevel',
                            'users.name',
                            'users.email',
                            )
                        ->get();

        return response()->json([
            'status' => 'success',
            'message' => 'user dashboard details',
            'performance' => $performance
        ]);
    }

    public function searchStaffPerfomByMonth(Request $request, $staffId)
    {
        $month = $request->input('month');

        $performance = DB::table('users')
                        ->join('performances', 'performances.user_id', '=', 'users.id')
                        ->whereMonth('performances.dateOfPerformance', '=', $month)
                        ->where('users.id', '=', $staffId)
                        ->select(
                            'performances.performanceLevel',
                            'users.name',
                            'users.email',
                            )
                        ->get();

        return response()->json([
            'status' => 'success',
            'message' => 'user dashboard details',
            'performance' => $performance
        ]);
    }

    public function searchStaffPerfomByYear(Request $request, $staffId)
    {
        $year = $request->input('year');

        $performance = DB::table('users')
                        ->join('performances', 'performances.user_id', '=', 'users.id')
                        ->whereYear('performances.dateOfPerformance', '=', $year)
                        ->where('users.id', '=', $staffId)
                        ->select(
                            'performances.performanceLevel',
                            'users.name',
                            'users.email',
                            )
                        ->get();

        return response()->json([
            'status' => 'success',
            'message' => 'user dashboard details',
            'performance' => $performance
        ]);
    }
}
