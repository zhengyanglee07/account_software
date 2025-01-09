<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\Gate;

class SettingController extends Controller
{
    public function index(){
        $permission = [];
        $permission['assignRole'] = Gate::allows('assign-role','assign-role');
        $permission['editPayment'] = Gate::allows('edit-payment','edit-payment');
        $env = app()->environment();


        return Inertia::render('setting/pages/AllSettings', compact('permission','env'));
    }
}
