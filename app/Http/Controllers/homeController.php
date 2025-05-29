<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Compra;

class homeController extends Controller
{
    public function index()
    {
        $users = User::count();
        $compras = Compra::count();

        return view('panel.index', compact('users', 'compras'));
    }
}
