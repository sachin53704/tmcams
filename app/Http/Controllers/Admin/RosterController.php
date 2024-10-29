<?php

namespace App\Http\Controllers\Admin;

use App\Exports\ExportEmployeeShift;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\ImportShiftRosterRequest;
use App\Imports\ImportEmployeeShift;
use App\Models\Department;
use App\Models\User;
use App\Models\Ward;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Facades\Excel;

class RosterController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return view('admin.rosters');
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('admin.rosters_create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }

    public function downloadSample(Request $request)
    {
        return Excel::download(new ExportEmployeeShift, 'sample_roster.xlsx');
    }

    public function importShiftRoster(ImportShiftRosterRequest $request)
    {
        $input = $request->validated();

        try
        {
            Excel::import(new ImportEmployeeShift($input), $request->file('file')->store('files'));
            return response()->json(['success'=> 'Employee shift roster updated successfully!']);
        }
        catch(\Exception $e)
        {
            return $this->respondWithAjax($e, 'updating', 'shift roster');
        }

    }
}
