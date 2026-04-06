<?php

namespace App\Http\Controllers;

use App\Models\User;

class UserController extends Controller
{
    public function staffList()
    {
        $staffs = User::where('role', 0)->get();
        return view('admin.staff.list', compact('staffs'));
    }
}
