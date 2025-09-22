<?php

namespace App\Http\Controllers;

use App\Application\Services\AuthenticationService;
use Illuminate\Http\Request;

class AdminController extends Controller
{
 private AuthenticationService $authService;

 public function __construct(AuthenticationService $authService)
 {
  $this->authService = $authService;
 }
}
