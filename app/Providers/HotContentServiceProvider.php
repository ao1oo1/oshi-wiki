<?php
namespace App\Providers;

use App\Http\Middleware\RecordPublicContentView;
use App\Services\HotContentService;
use Illuminate\Routing\Router;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class HotContentServiceProvider extends ServiceProvider
{
    public function boot(Router $router, HotContentService $hot): void
    {
        $router->pushMiddlewareToGroup('web',RecordPublicContentView::class);

        View::composer('public.works.index.blade',function($view) use($hot): void {
            $view->with([
                'hotWorks'=>$hot->works(),
                'hotCharacters'=>$hot->characters(),
                'hotPeriodDays'=>HotContentService::PERIOD_DAYS,
            ]);
        });
    }
}
