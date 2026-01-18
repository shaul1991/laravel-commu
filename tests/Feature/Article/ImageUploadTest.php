<?php

declare(strict_types=1);

namespace Tests\Feature\Article;

use App\Infrastructure\Persistence\Eloquent\UserModel;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

final class ImageUploadTest extends TestCase
{
    use RefreshDatabase;

    private UserModel $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = UserModel::factory()->create();
        Storage::fake('public');
    }

    public function test_인증된_사용자는_이미지를_업로드할_수_있다(): void
    {
        $this->actingAs($this->user, 'api');

        $file = UploadedFile::fake()->image('test-image.jpg', 800, 600);

        $response = $this->postJson('/api/images/upload', [
            'image' => $file,
        ]);

        $response->assertCreated()
            ->assertJsonStructure([
                'data' => [
                    'url',
                    'path',
                    'filename',
                    'size',
                    'mime_type',
                ],
            ]);

        $path = $response->json('data.path');
        Storage::disk('public')->assertExists($path);
    }

    public function test_인증되지_않은_사용자는_이미지를_업로드할_수_없다(): void
    {
        $file = UploadedFile::fake()->image('test-image.jpg');

        $response = $this->postJson('/api/images/upload', [
            'image' => $file,
        ]);

        $response->assertUnauthorized();
    }

    public function test_이미지가_아닌_파일은_업로드할_수_없다(): void
    {
        $this->actingAs($this->user, 'api');

        $file = UploadedFile::fake()->create('document.pdf', 100, 'application/pdf');

        $response = $this->postJson('/api/images/upload', [
            'image' => $file,
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['image']);
    }

    public function test_5_m_b_이상의_이미지는_업로드할_수_없다(): void
    {
        $this->actingAs($this->user, 'api');

        // Create a 6MB image file
        $file = UploadedFile::fake()->image('large-image.jpg')->size(6 * 1024);

        $response = $this->postJson('/api/images/upload', [
            'image' => $file,
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['image']);
    }

    public function test_지원되는_이미지_형식만_업로드할_수_있다(): void
    {
        $this->actingAs($this->user, 'api');

        // Test supported formats
        $formats = ['jpg', 'jpeg', 'png', 'gif', 'webp'];

        foreach ($formats as $format) {
            $file = UploadedFile::fake()->image("test.{$format}");

            $response = $this->postJson('/api/images/upload', [
                'image' => $file,
            ]);

            $response->assertCreated();
        }
    }

    public function test_이미지_파일명이_없으면_업로드할_수_없다(): void
    {
        $this->actingAs($this->user, 'api');

        $response = $this->postJson('/api/images/upload', []);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['image']);
    }
}
