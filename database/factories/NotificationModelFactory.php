<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Infrastructure\Persistence\Eloquent\NotificationModel;
use App\Infrastructure\Persistence\Eloquent\UserModel;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<NotificationModel>
 */
final class NotificationModelFactory extends Factory
{
    protected $model = NotificationModel::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $types = ['comment', 'reply', 'follow', 'like'];
        $type = fake()->randomElement($types);

        return [
            'uuid' => (string) Str::uuid(),
            'user_id' => UserModel::factory(),
            'type' => $type,
            'message' => $this->getMessageForType($type),
            'data' => ['actor_id' => fake()->uuid()],
            'is_read' => fake()->boolean(30),
        ];
    }

    public function unread(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_read' => false,
        ]);
    }

    public function read(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_read' => true,
        ]);
    }

    private function getMessageForType(string $type): string
    {
        return match ($type) {
            'comment' => '회원님의 게시글에 댓글이 달렸습니다.',
            'reply' => '회원님의 댓글에 답글이 달렸습니다.',
            'follow' => '회원님을 팔로우하기 시작했습니다.',
            'like' => '회원님의 게시글을 좋아합니다.',
            default => '새로운 알림이 있습니다.',
        };
    }
}
