<?php

namespace App\Http\Controllers\v1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Paylater\StorePaylaterRequest;
use App\Http\Requests\Paylater\UpdatePaylaterRequest;
use App\Models\Paylater;

class PaylaterController extends Controller
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
    public function store(StorePaylaterRequest $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(Paylater $paylater)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdatePaylaterRequest $request, Paylater $paylater)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Paylater $paylater)
    {
        //
    }
}
