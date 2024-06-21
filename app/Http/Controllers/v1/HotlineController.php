<?php

namespace App\Http\Controllers\v1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Hotline\StoreHotlineRequest;
use App\Http\Requests\Hotline\UpdateHotlineRequest;
use App\Models\Hotline;

class HotlineController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreHotlineRequest $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(Hotline $hotline)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateHotlineRequest $request, Hotline $hotline)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Hotline $hotline)
    {
        //
    }
}
