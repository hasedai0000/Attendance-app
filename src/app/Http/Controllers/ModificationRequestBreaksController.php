<?php

namespace App\Http\Controllers;

use App\Application\Services\ModificationRequestBreaksService;

class ModificationRequestBreaksController extends Controller
{
    private ModificationRequestBreaksService $modificationRequestBreaksService;

    public function __construct(
        ModificationRequestBreaksService $modificationRequestBreaksService
    ) {
        $this->modificationRequestBreaksService = $modificationRequestBreaksService;
    }
}
