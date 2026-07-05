<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\MessageLog;
use Illuminate\Http\Request;
use Illuminate\View\View;

class MessageLogController extends Controller
{
    public function index(Request $request): View
    {
        $filters = [
            'channel' => $request->query('channel'),
            'status' => $request->query('status'),
        ];

        $messages = MessageLog::query()
            ->with('business:id,name,slug')
            ->when($filters['channel'], fn ($q, $c) => $q->where('channel', $c))
            ->when($filters['status'], fn ($q, $s) => $q->where('status', $s))
            ->latest()
            ->paginate(30)
            ->withQueryString();

        return view('superadmin.messages', compact('messages', 'filters'));
    }
}
