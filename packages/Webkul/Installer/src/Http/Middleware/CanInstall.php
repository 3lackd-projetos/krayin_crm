<?php

namespace Webkul\Installer\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Str;
use Webkul\Installer\Helpers\DatabaseManager;

class CanInstall
{
    /**
     * Handles Requests if application is already installed then redirect to dashboard else to installer.
     */
    public function handle(Request $request, Closure $next): mixed
    {
        if (Str::contains($request->getPathInfo(), '/install')) {
            if ($this->isAlreadyInstalled() && !$request->ajax()) {
                return redirect()->route('admin.dashboard.index');
            }
        } else {
            if (!$this->isAlreadyInstalled()) {
                return redirect()->route('installer.index');
            }
        }

        return $next($request);
    }

    /**
     * Check if application is already installed.
     */
    public function isAlreadyInstalled(): bool
    {
        if (file_exists(storage_path('installed'))) {
            \Illuminate\Support\Facades\Log::info('Krayin: Installation file found in storage.');
            return true;
        }

        if (file_exists(base_path('storage/installed'))) {
            \Illuminate\Support\Facades\Log::info('Krayin: Installation file found in base_path.');
            return true;
        }

        \Illuminate\Support\Facades\Log::info('Krayin: Checking database for installation state...');
        if (app(DatabaseManager::class)->isInstalled()) {
            \Illuminate\Support\Facades\Log::info('Krayin: Database is ready. Attempting to create installation flag.');
            @touch(storage_path('installed'));
            @touch(base_path('storage/installed'));

            Event::dispatch('krayin.installed');

            return true;
        }

        \Illuminate\Support\Facades\Log::warning('Krayin: Installation not detected (no file, no DB users).');
        return false;
    }
}
