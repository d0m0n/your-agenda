<?php

namespace Database\Factories;

use App\Models\Organization;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Organization>
 */
class OrganizationFactory extends Factory
{
    protected $model = Organization::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->city().'青年会議所',
            // 既存の大量のテスト(CreatesTenants等)がsubscribedミドルウェアで
            // ペイウォールに弾かれないよう、デフォルトでトライアル中の状態にする。
            // 実際の登録フローは14日固定(RegisteredUserController)だが、こちらは
            // あくまでテスト用のデフォルト値なので、Carbon::setTestNow()で数か月先に
            // 時間移動するテスト(季節の挨拶など本機能と無関係なテスト)がうっかり
            // トライアル期限を越えてしまわないよう、十分先の日付にしておく。
            // トライアル終了状態をテストしたい場合はexpiredTrial()ステートを使う。
            'trial_ends_at' => now()->addYear(),
        ];
    }

    public function expiredTrial(): static
    {
        return $this->state(fn (array $attributes) => [
            'trial_ends_at' => now()->subDay(),
        ]);
    }
}
