<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class SettingsController extends Controller
{
    public function updateGeneral(Request $request)
    {
        $request->validate([
            'site_name' => 'nullable|string|max:255',
            'site_description' => 'nullable|string|max:1000',
            'currency' => 'nullable|string|max:10',
            'support_email' => 'nullable|email|max:255',
        ]);

        $settings = Cache::get('admin_settings_general', []);
        $settings = array_merge($settings, $request->only(['site_name', 'site_description', 'currency', 'support_email']));
        Cache::forever('admin_settings_general', $settings);

        return response()->json(['message' => 'General settings updated', 'data' => $settings], 200);
    }

    public function updateTransaction(Request $request)
    {
        $request->validate([
            'payment_instructions' => 'nullable|string|max:2000',
            'tax_rate' => 'nullable|numeric|min:0|max:100',
            'shipping_fee' => 'nullable|numeric|min:0',
        ]);

        $settings = Cache::get('admin_settings_transaction', []);
        $settings = array_merge($settings, $request->only(['payment_instructions', 'tax_rate', 'shipping_fee']));
        Cache::forever('admin_settings_transaction', $settings);

        return response()->json(['message' => 'Transaction settings updated', 'data' => $settings], 200);
    }

    public function toggleMaintenance(Request $request)
    {
        $request->validate([
            'enabled' => 'required|boolean',
        ]);

        $status = $request->boolean('enabled');
        Cache::forever('admin_maintenance_mode', $status);

        return response()->json(['message' => $status ? 'Maintenance mode enabled' : 'Maintenance mode disabled', 'maintenance' => $status], 200);
    }
}
