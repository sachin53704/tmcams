<?php

namespace App\Http\Controllers\Admin\Masters;

use App\Http\Controllers\Admin\Controller;
use App\Http\Requests\Admin\Masters\StoreWardRequest;
use App\Http\Requests\Admin\Masters\UpdateWardRequest;
use App\Models\Ward;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;


class WardController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $wards = Ward::where('tenant_id', auth()->user()->tenant_id)->latest()->get();

        return view('admin.masters.wards')->with(['wards'=> $wards]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreWardRequest $request)
    {
        try
        {
            DB::beginTransaction();
            $input = $request->validated();
            $input['tenant_id'] = auth()->user()->tenant_id ?? 1;
            Ward::create( Arr::only( $input, Ward::getFillables() ) );
            DB::commit();

            return response()->json(['success'=> 'Office created successfully!']);
        }
        catch(\Exception $e)
        {
            return $this->respondWithAjax($e, 'creating', 'Office');
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {

    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Ward $ward)
    {
        if ($ward)
        {
            $response = [
                'result' => 1,
                'ward' => $ward,
            ];
        }
        else
        {
            $response = ['result' => 0];
        }
        return $response;
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateWardRequest $request, Ward $ward)
    {
        try
        {
            DB::beginTransaction();
            $input = $request->validated();
            $input['name'] = $input['edit_name'];
            $ward->update( Arr::only( $input, Ward::getFillables() ) );
            DB::commit();

            return response()->json(['success'=> 'Office updated successfully!']);
        }
        catch(\Exception $e)
        {
            return $this->respondWithAjax($e, 'updating', 'Office');
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Ward $ward)
    {
        try
        {
            DB::beginTransaction();
            $ward->delete();
            DB::commit();
            return response()->json(['success'=> 'Office deleted successfully!']);
        }
        catch(\Exception $e)
        {
            return $this->respondWithAjax($e, 'deleting', 'Office');
        }
    }
}
