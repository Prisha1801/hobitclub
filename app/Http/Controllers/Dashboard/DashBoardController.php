<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Users;

class DashBoardController extends Controller
{
    public function overview()
    {
        $Customers = User::where('role','customer')->get(); 
        
        dd($Customers);
    }


}
