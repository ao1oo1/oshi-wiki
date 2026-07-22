<?php

namespace App\Support;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Str;

final class AdminBreadcrumbs
{
    /**
     * @return array<int, array{label: string, url: ?string}>
     */
    public static function items(): array
    {
        $route = request()->route();

        if ($route === null || ! request()->routeIs('admin.*')) {
            return [];
        }

        $name = (string) $route->getName();
        $items = [
            self::item('ダッシュボード', self::routeUrl('admin.dashboard')),
        ];

        if ($name === 'admin.dashboard') {
            return self::markCurrent($items);
        }

        $items = array_merge($items, self::sectionItems($name));

        return self::markCurrent(self::unique($items));
    }

    /**
     * @return array<int, array{label: string, url: ?string}>
     */
    private static function sectionItems(string $name): array
    {
        return match (true) {
            Str::startsWith($name, 'admin.works.story-sections.events.csv.') =>
                self::storySectionEventCsvItems($name),

            Str::startsWith($name, 'admin.works.story-sections.') =>
                self::storySectionItems($name),

            Str::startsWith($name, 'admin.works.monetization-links.'),
            Str::startsWith($name, 'admin.works.monetization-settings.') =>
                self::workMonetizationItems($name),

            Str::startsWith($name, 'admin.works.') =>
                self::resourceItems(
                    name: $name,
                    routePrefix: 'admin.works',
                    indexLabel: '作品管理',
                    parameter: 'work',
                    modelLabel: self::modelLabel('work', ['title', 'name'], '作品'),
                ),

            Str::startsWith($name, 'admin.characters.') =>
                self::resourceItems(
                    name: $name,
                    routePrefix: 'admin.characters',
                    indexLabel: 'キャラクター管理',
                    parameter: 'character',
                    modelLabel: self::modelLabel('character', ['name', 'character_name'], 'キャラクター'),
                ),

            Str::startsWith($name, 'admin.character-relationships.') =>
                self::resourceItems(
                    name: $name,
                    routePrefix: 'admin.character-relationships',
                    indexLabel: '関係性管理',
                    parameter: 'character_relationship',
                    modelLabel: self::relationshipLabel(),
                ),

            Str::startsWith($name, 'admin.tags.') =>
                self::resourceItems(
                    name: $name,
                    routePrefix: 'admin.tags',
                    indexLabel: 'タグ管理',
                    parameter: 'tag',
                    modelLabel: self::modelLabel('tag', ['name'], 'タグ'),
                ),

            Str::startsWith($name, 'admin.review-requests.') =>
                [self::item('承認待ち', self::routeUrl('admin.review-requests.index'))],

            Str::startsWith($name, 'admin.contributor-applications.') =>
                [self::item('スタッフ申請', self::routeUrl('admin.contributor-applications.index'))],

            Str::startsWith($name, 'admin.contact-messages.') =>
                self::contactMessageItems($name),

            Str::startsWith($name, 'admin.trash.') =>
                [self::item('ゴミ箱', self::routeUrl('admin.trash.index'))],

            Str::startsWith($name, 'admin.monetization.services.') =>
                self::simpleResourceItems(
                    $name,
                    'admin.monetization.services',
                    '収益サービス管理',
                    self::modelLabel('service', ['name'], 'サービス')
                ),

            Str::startsWith($name, 'admin.monetization.programs.') =>
                self::simpleResourceItems(
                    $name,
                    'admin.monetization.programs',
                    'アフィリエイト設定',
                    self::modelLabel('program', ['name'], 'アフィリエイト設定')
                ),

            Str::startsWith($name, 'admin.monetization.analytics.') =>
                [self::item('アナリティクス', null)],

            Str::startsWith($name, 'admin.analytics.') =>
                [self::item('Writerアナリティクス', self::routeUrl('admin.analytics.index'))],

            $name === 'admin.staff-guide' =>
                [self::item('スタッフガイド', null)],

            Str::startsWith($name, 'admin.staff-profile.') =>
                [self::item('プロフィール設定', self::routeUrl('admin.staff-profile.edit'))],

            Str::contains($name, 'maintenance') =>
                [self::item('メンテナンス管理', null)],

            default => [self::item(self::fallbackLabel($name), null)],
        };
    }

    /**
     * @return array<int, array{label: string, url: ?string}>
     */
    private static function storySectionItems(string $name): array
    {
        $items = self::workBaseItems();
        $items[] = self::item(
            '章・編ごとの物語詳細',
            self::routeUrl('admin.works.story-sections.index', self::routeParameter('work'))
        );

        if (Str::contains($name, '.csv.')) {
            $items[] = self::item(self::actionLabel($name), null);
            return $items;
        }

        if (Str::contains($name, '.text-import.')) {
            $items[] = self::item('テキスト取り込み', null);
            return $items;
        }

        $section = self::routeParameter('storySection');
        if ($section instanceof Model) {
            $items[] = self::item(
                self::labelFromModel($section, ['title', 'name'], '章・編'),
                self::routeUrl(
                    'admin.works.story-sections.show',
                    [$itemsWork = self::routeParameter('work'), $section]
                )
            );
        }

        $items[] = self::item(self::actionLabel($name), null);

        return $items;
    }

    /**
     * @return array<int, array{label: string, url: ?string}>
     */
    private static function storySectionEventCsvItems(string $name): array
    {
        $items = self::workBaseItems();
        $work = self::routeParameter('work');
        $section = self::routeParameter('storySection');

        $items[] = self::item(
            '章・編ごとの物語詳細',
            self::routeUrl('admin.works.story-sections.index', $work)
        );

        if ($section instanceof Model) {
            $items[] = self::item(
                self::labelFromModel($section, ['title', 'name'], '章・編'),
                self::routeUrl('admin.works.story-sections.show', [$work, $section])
            );
        }

        $items[] = self::item(
            Str::endsWith($name, '.export') ? 'CSV出力' : 'CSV取り込み',
            null
        );

        return $items;
    }

    /**
     * @return array<int, array{label: string, url: ?string}>
     */
    private static function workMonetizationItems(string $name): array
    {
        $items = self::workBaseItems();
        $items[] = self::item('商品リンク管理', null);

        if (Str::endsWith($name, '.edit')) {
            $items[] = self::item('編集', null);
        }

        return $items;
    }

    /**
     * @return array<int, array{label: string, url: ?string}>
     */
    private static function workBaseItems(): array
    {
        $work = self::routeParameter('work');
        $items = [
            self::item('作品管理', self::routeUrl('admin.works.index')),
        ];

        if ($work instanceof Model) {
            $items[] = self::item(
                self::labelFromModel($work, ['title', 'name'], '作品'),
                self::routeUrl('admin.works.show', $work)
            );
        }

        return $items;
    }

    /**
     * @return array<int, array{label: string, url: ?string}>
     */
    private static function resourceItems(
        string $name,
        string $routePrefix,
        string $indexLabel,
        string $parameter,
        string $modelLabel
    ): array {
        $items = [
            self::item($indexLabel, self::routeUrl($routePrefix.'.index')),
        ];

        if (self::routeParameter($parameter) instanceof Model) {
            $showRoute = $routePrefix.'.show';
            $items[] = self::item(
                $modelLabel,
                self::routeUrl($showRoute, self::routeParameter($parameter))
            );
        }

        if (! Str::endsWith($name, '.index') && ! Str::endsWith($name, '.show')) {
            $items[] = self::item(self::actionLabel($name), null);
        }

        return $items;
    }

    /**
     * @return array<int, array{label: string, url: ?string}>
     */
    private static function simpleResourceItems(
        string $name,
        string $routePrefix,
        string $indexLabel,
        string $modelLabel
    ): array {
        $items = [
            self::item($indexLabel, self::routeUrl($routePrefix.'.index')),
        ];

        if (Str::endsWith($name, '.edit')) {
            $items[] = self::item($modelLabel, null);
        }

        return $items;
    }

    /**
     * @return array<int, array{label: string, url: ?string}>
     */
    private static function contactMessageItems(string $name): array
    {
        $items = [
            self::item('お問い合わせ受信箱', self::routeUrl('admin.contact-messages.index')),
        ];

        if (Str::endsWith($name, '.show')) {
            $message = self::routeParameter('contactMessage');
            $items[] = self::item(
                self::labelFromModel($message, ['subject', 'name'], 'お問い合わせ詳細'),
                null
            );
        }

        return $items;
    }

    private static function relationshipLabel(): string
    {
        $relationship = self::routeParameter('character_relationship');

        if (! $relationship instanceof Model) {
            $relationship = self::routeParameter('characterRelationship');
        }

        if (! $relationship instanceof Model) {
            return '関係性';
        }

        return self::labelFromModel(
            $relationship,
            ['title', 'name', 'relationship_type'],
            '関係性'
        );
    }

    private static function modelLabel(
        string $parameter,
        array $attributes,
        string $fallback
    ): string {
        return self::labelFromModel(
            self::routeParameter($parameter),
            $attributes,
            $fallback
        );
    }

    private static function labelFromModel(
        mixed $value,
        array $attributes,
        string $fallback
    ): string {
        if (! $value instanceof Model) {
            return $fallback;
        }

        foreach ($attributes as $attribute) {
            $label = trim((string) data_get($value, $attribute, ''));

            if ($label !== '') {
                return $label;
            }
        }

        return $fallback;
    }

    private static function routeParameter(string $key): mixed
    {
        return request()->route($key);
    }

    private static function routeUrl(string $name, mixed $parameters = []): ?string
    {
        if (! Route::has($name)) {
            return null;
        }

        try {
            return route($name, $parameters);
        } catch (\Throwable) {
            return null;
        }
    }

    private static function actionLabel(string $name): string
    {
        return match (true) {
            Str::endsWith($name, '.create') => '新規登録',
            Str::endsWith($name, '.edit') => '編集',
            Str::endsWith($name, '.show') => '詳細',
            Str::contains($name, '.csv-import.') => 'CSV取り込み',
            Str::contains($name, '.csv-export') => 'CSV出力',
            Str::contains($name, '.import.') => 'テキスト取り込み',
            Str::contains($name, '.text-import.') => 'テキスト取り込み',
            Str::contains($name, '.csv.') && Str::endsWith($name, '.export') => 'CSV出力',
            Str::contains($name, '.csv.') => 'CSV取り込み・出力',
            default => self::fallbackLabel($name),
        };
    }

    private static function fallbackLabel(string $name): string
    {
        $last = Str::afterLast($name, '.');

        return match ($last) {
            'index' => '一覧',
            'create' => '新規登録',
            'edit' => '編集',
            'show' => '詳細',
            'export' => 'CSV出力',
            default => Str::headline($last),
        };
    }

    /**
     * @param array<int, array{label: string, url: ?string}> $items
     * @return array<int, array{label: string, url: ?string}>
     */
    private static function markCurrent(array $items): array
    {
        if ($items === []) {
            return [];
        }

        $last = array_key_last($items);
        $items[$last]['url'] = null;

        return $items;
    }

    /**
     * @param array<int, array{label: string, url: ?string}> $items
     * @return array<int, array{label: string, url: ?string}>
     */
    private static function unique(array $items): array
    {
        $result = [];

        foreach ($items as $item) {
            $key = $item['label'].'|'.($item['url'] ?? '');

            if (! isset($result[$key])) {
                $result[$key] = $item;
            }
        }

        return array_values($result);
    }

    /**
     * @return array{label: string, url: ?string}
     */
    private static function item(string $label, ?string $url): array
    {
        return [
            'label' => $label,
            'url' => $url,
        ];
    }
}
