<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Region;

class RegionController extends Controller
{
    /**
     * GET /api/admin/regions
     */
    public function index()
    {
        return response()->json(
            Region::orderBy('label_region')->get()
        );
    }
}
