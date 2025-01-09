<?php

namespace App\Http\Controllers\MasterAdmin;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

// use \App\MasterAdmin\MasterAdmin;
class DashboardController extends Controller
{
    /**
     * Show Admin Dashboard.
     * 
     * @return \Illuminate\Http\Response
     */
    public function index(){
		
		$tables = DB::select('SHOW TABLES');
		$tableNames = array_map('current',$tables);
		// dd($tables);
		return Inertia::render('master-admin/pages/Dashboard', compact('tableNames'));		
	}
}