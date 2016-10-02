<?php
/*
 * Bootstrap file
 */
namespace App;

// $request = new System\Request;

use App\System\Route;

Route::get("index", "Home::index");

Route::get("user/:id/:name", "Home::tamer")->middleware("Auth", "Ruler");


// System\Middleware::invoke("Auth::handle");

// (new \App\Middleware\Auth())->handle();

// $resp = (new \App\System\Middleware(["Auth", "Ruler"]))->beginQueue();
