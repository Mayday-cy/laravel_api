<?php

namespace App\Http\Controllers;

use App\Components\ApiHelpers\ApiResponse;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class Controller extends BaseController
{
    use ApiResponse;
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;
}
