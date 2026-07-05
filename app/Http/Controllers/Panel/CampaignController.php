<?php

namespace App\Http\Controllers\Panel;

use App\Http\Controllers\Controller;
use App\Jobs\SendCampaign;
use App\Models\Business;
use App\Models\Campaign;
use App\Models\Customer;
use App\Services\Messaging\WhatsAppManager;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class CampaignController extends Controller
{
    public function index(Business $business, WhatsAppManager $whatsapp): View
    {
        $campaigns = Campaign::query()
            ->with('creator:id,name')
            ->latest()
            ->paginate(15);

        $audienceCounts = [
            'all' => Customer::query()->where('is_blacklisted', false)->count(),
            'loyal' => Customer::query()->where('is_blacklisted', false)->where('completed_appointments', '>=', 3)->count(),
            'recent' => Customer::query()->where('is_blacklisted', false)
                ->whereHas('appointments', fn ($q) => $q->where('starts_at', '>=', now()->subDays(90)))
                ->count(),
        ];

        $quota = (int) ($business->plan?->whatsapp_quota_monthly ?? 0);

        return view('panel.campaigns', [
            'business' => $business,
            'campaigns' => $campaigns,
            'audienceCounts' => $audienceCounts,
            'quota' => $quota,
            'usedThisMonth' => $whatsapp->usedThisMonth($business),
        ]);
    }

    public function store(Business $business, Request $request): RedirectResponse
    {
        if (! $business->whatsapp_enabled) {
            return back()->withErrors(['message' => 'Kampanya göndermek için önce ayarlardan WhatsApp bildirimlerini açın.']);
        }

        $data = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'audience' => ['required', Rule::in(array_keys(Campaign::audiences()))],
            'message' => ['required', 'string', 'max:1000'],
        ]);

        $campaign = new Campaign($data);
        $campaign->business_id = $business->id;
        $campaign->created_by = $request->user()->id;
        $campaign->save();

        SendCampaign::dispatch($campaign->id);

        return back()->with('success', 'Kampanya kuyruğa alındı; gönderim arka planda yapılıyor.');
    }

    public function destroy(Business $business, Campaign $campaign): RedirectResponse
    {
        if ($campaign->status === 'sending') {
            return back()->withErrors(['message' => 'Gönderimi süren kampanya silinemez.']);
        }

        $campaign->delete();

        return back()->with('success', 'Kampanya silindi.');
    }
}
