<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\Relations\Relation;
use App\Models\Project;
use App\Models\Product;
use App\Models\Material;
use App\Models\Good;
use App\Observers\ProjectObserver;
use Illuminate\Support\Facades\URL;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Ép buộc toàn bộ link phải chạy HTTPS
        if ($this->app->environment('production') || true) {
            URL::forceScheme('https');
        }

        // Register morphMap for polymorphic relations
        Relation::morphMap([
            'project' => Project::class,
            'product' => Product::class,
            'material' => Material::class,
            'good' => Good::class,
        ]);

        // Đăng ký observer cho model Project
        Project::observe(ProjectObserver::class);

        // Định nghĩa Gate cho hệ thống quyền tùy chỉnh
        Gate::define('*', function ($user, $permission) {
            // Nếu là admin, cho phép tất cả
            if ($user->role === 'admin') {
                return true;
            }

            // Kiểm tra quyền thông qua nhóm quyền
            if ($user->role_id && $user->roleGroup) {
                return $user->roleGroup->hasPermission($permission);
            }

            return false;
        });
    }
}
