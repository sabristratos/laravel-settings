<?php

namespace Strata\Settings\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\View;
use Strata\Settings\Facades\Settings;
use Symfony\Component\HttpFoundation\Response;

class ShareSettingsMiddleware
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (! config('settings.share.enabled', false)) {
            return $next($request);
        }

        $sharedSettings = $this->getSharedSettings();

        View::share('settings', $sharedSettings);

        return $next($request);
    }

    /**
     * Get settings to share with views.
     */
    protected function getSharedSettings(): array
    {
        $publicOnly = config('settings.share.public_only', true);
        $keys = config('settings.share.keys', []);
        $groups = config('settings.share.groups', []);

        if (empty($keys) && empty($groups)) {
            return $publicOnly ? Settings::allPublic()->toArray() : Settings::all()->toArray();
        }

        $settings = [];

        if (! empty($keys)) {
            foreach ($keys as $key) {
                $settings[$key] = Settings::get($key);
            }
        }

        if (! empty($groups)) {
            foreach ($groups as $group) {
                $groupSettings = $publicOnly
                    ? Settings::allPublic($group)->toArray()
                    : Settings::group($group)->toArray();

                $settings = array_merge($settings, $groupSettings);
            }
        }

        return $settings;
    }
}
