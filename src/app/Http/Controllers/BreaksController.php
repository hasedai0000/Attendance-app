<?php

namespace App\Http\Controllers;

use App\Application\Services\BreaksService;

class BreaksController extends Controller
{
    private BreaksService $breaksService;

    public function __construct(
        BreaksService $breaksService
    ) {
        $this->breaksService = $breaksService;
    }
}
