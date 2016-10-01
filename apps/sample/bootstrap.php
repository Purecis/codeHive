<?php
/*
 * Bootstrap file
 */
namespace App;

// $request = new System\Request;

use App\System\Route;

Route::get("index", "Home::index");

Route::get("user/:id/:name", "Home::tamer");

