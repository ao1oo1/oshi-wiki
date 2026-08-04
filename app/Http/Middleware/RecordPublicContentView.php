<?php
namespace App\Http\Middleware;

use App\Models\Character;
use App\Models\ContentViewDailyStat;
use App\Models\Work;
use Closure;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Symfony\Component\HttpFoundation\Response;

class RecordPublicContentView
{
    private const ROUTES=['public.works.show','public.characters.show'];
    private const THROTTLE_MINUTES=30;

    public function handle(Request $request, Closure $next): Response
    {
        $response=$next($request);
        if (!$this->shouldRecord($request,$response)) return $response;

        $viewable=$this->resolveViewable($request);
        if (!$viewable) return $response;

        $visitor=hash('sha256',implode('|',[
            (string)$request->ip(),
            (string)$request->userAgent(),
            (string)$request->session()->getId(),
        ]));
        $key='content-view:'.str_replace('\\','-',$viewable::class)
            .':'.$viewable->getKey().':'.$visitor;

        if (!Cache::add($key,true,now()->addMinutes(self::THROTTLE_MINUTES))) {
            return $response;
        }

        $attrs=[
            'viewable_type'=>$viewable::class,
            'viewable_id'=>$viewable->getKey(),
            'viewed_on'=>today()->toDateString(),
        ];

        try {
            $stat=ContentViewDailyStat::query()->firstOrCreate(
                $attrs,['view_count'=>0]
            );
        } catch (QueryException) {
            $stat=ContentViewDailyStat::query()->where($attrs)->firstOrFail();
        }
        $stat->increment('view_count');

        return $response;
    }

    private function shouldRecord(Request $request, Response $response): bool
    {
        if (
            !$request->isMethod('GET')
            || $response->getStatusCode()!==200
            || !in_array($request->route()?->getName(),self::ROUTES,true)
        ) return false;

        if (auth()->check() && auth()->user()?->canManageAllAdminFeatures()) {
            return false;
        }

        return !preg_match(
            '/bot|crawler|spider|slurp|preview|monitor|uptime|headless/i',
            strtolower((string)$request->userAgent())
        );
    }

    private function resolveViewable(Request $request): Work|Character|null
    {
        foreach ($request->route()?->parameters() ?? [] as $value) {
            if ($value instanceof Work || $value instanceof Character) return $value;
        }
        return null;
    }
}
