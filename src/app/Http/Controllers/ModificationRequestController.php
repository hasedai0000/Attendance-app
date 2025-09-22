<?php

namespace App\Http\Controllers;

use App\Application\Services\ModificationRequestService;

class ModificationRequestController extends Controller
{
    private ModificationRequestService $modificationRequestService;

    public function __construct(
        ModificationRequestService $modificationRequestService
    ) {
        $this->modificationRequestService = $modificationRequestService;
    }
}
