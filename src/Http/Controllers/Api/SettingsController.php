<?php

namespace Strata\Settings\Http\Controllers\Api;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Strata\Settings\Facades\Settings;
use Strata\Settings\Http\Resources\SettingResource;
use Strata\Settings\Models\Setting;

class SettingsController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $group = $request->query('group');
        $publicOnly = $request->query('public', false);

        if ($publicOnly) {
            $query = Setting::public();
            if ($group) {
                $query->group($group);
            }
            $settings = $query->ordered()->get();
        } else {
            $query = Setting::query();
            if ($group) {
                $query->group($group);
            }
            $settings = $query->ordered()->get();
        }

        return response()->json(SettingResource::collection($settings));
    }

    public function show(string $key): JsonResponse
    {
        $setting = Setting::key($key)->firstOrFail();

        return response()->json(new SettingResource($setting));
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'key' => 'required|string|unique:settings,key',
            'value' => 'required',
            'group' => 'nullable|string',
            'encrypted' => 'boolean',
        ]);

        $setting = Settings::set(
            $validated['key'],
            $validated['value'],
            $validated['group'] ?? null,
            $validated['encrypted'] ?? false
        );

        return response()->json(new SettingResource($setting), 201);
    }

    public function update(Request $request, string $key): JsonResponse
    {
        $validated = $request->validate([
            'value' => 'required',
            'group' => 'nullable|string',
        ]);

        $setting = Settings::set(
            $key,
            $validated['value'],
            $validated['group'] ?? null
        );

        return response()->json(new SettingResource($setting));
    }

    public function destroy(string $key): JsonResponse
    {
        Settings::forget($key);

        return response()->json(['message' => 'Setting deleted successfully']);
    }
}
