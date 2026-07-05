<?php

namespace App\Http\Controllers\Landing;

use App\Http\Controllers\Controller;
use App\Models\SubscriptionPlan;
use Illuminate\View\View;

class LandingController extends Controller
{
    public function index(): View
    {
        return view('landing.index', [
            'plans' => SubscriptionPlan::query()->active()->get(),
        ]);
    }
}
