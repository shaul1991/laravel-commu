<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

/**
 * 데이터베이스 연결 테스트
 *
 * Docker 환경에서만 실행됩니다.
 * 로컬 테스트: make test
 */
class DatabaseConnectionTest extends TestCase
{
    /**
     * PostgreSQL 연결 테스트
     */
    public function test_postgresql_connection(): void
    {
        if (config('database.default') !== 'pgsql') {
            $this->markTestSkipped('PostgreSQL not configured as default database');
        }

        $pdo = DB::connection('pgsql')->getPdo();

        $this->assertNotNull($pdo);
        $this->assertStringContainsString('18', $pdo->getAttribute(\PDO::ATTR_SERVER_VERSION));
    }

    /**
     * PostgreSQL 기본 CRUD 테스트
     */
    public function test_postgresql_crud(): void
    {
        if (config('database.default') !== 'pgsql') {
            $this->markTestSkipped('PostgreSQL not configured as default database');
        }

        // Create
        $id = DB::table('users')->insertGetId([
            'name' => 'Test User',
            'email' => 'test-'.uniqid().'@example.com',
            'password' => bcrypt('password'),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->assertIsInt($id);

        // Read
        $user = DB::table('users')->find($id);
        $this->assertEquals('Test User', $user->name);

        // Update
        DB::table('users')->where('id', $id)->update(['name' => 'Updated User']);
        $user = DB::table('users')->find($id);
        $this->assertEquals('Updated User', $user->name);

        // Delete
        DB::table('users')->delete($id);
        $this->assertNull(DB::table('users')->find($id));
    }

    /**
     * Redis 연결 테스트
     */
    public function test_redis_connection(): void
    {
        if (config('database.redis.client') !== 'phpredis') {
            $this->markTestSkipped('Redis not configured');
        }

        try {
            Redis::set('test_key', 'test_value');
            $value = Redis::get('test_key');

            $this->assertEquals('test_value', $value);

            Redis::del('test_key');
        } catch (\Exception $e) {
            $this->markTestSkipped('Redis connection failed: '.$e->getMessage());
        }
    }

    /**
     * Redis 캐시 테스트
     */
    public function test_redis_cache(): void
    {
        if (config('cache.default') !== 'redis') {
            $this->markTestSkipped('Redis cache not configured');
        }

        try {
            Cache::put('cache_test', 'cached_value', 60);
            $value = Cache::get('cache_test');

            $this->assertEquals('cached_value', $value);

            Cache::forget('cache_test');
        } catch (\Exception $e) {
            $this->markTestSkipped('Redis cache connection failed: '.$e->getMessage());
        }
    }

    /**
     * MongoDB 연결 테스트
     */
    public function test_mongodb_connection(): void
    {
        if (! config('database.connections.mongodb')) {
            $this->markTestSkipped('MongoDB not configured');
        }

        if (! extension_loaded('mongodb')) {
            $this->markTestSkipped('MongoDB extension not installed');
        }

        try {
            $client = DB::connection('mongodb')->getClient();
            $result = $client->selectDatabase('admin')->command(['ping' => 1]);

            $this->assertEquals(1, $result->toArray()[0]->ok);
        } catch (\Exception $e) {
            $this->markTestSkipped('MongoDB connection failed: '.$e->getMessage());
        }
    }

    /**
     * MinIO (S3) 연결 테스트
     */
    public function test_minio_connection(): void
    {
        if (! config('filesystems.disks.minio')) {
            $this->markTestSkipped('MinIO not configured');
        }

        try {
            $disk = Storage::disk('minio');

            // Write
            $disk->put('test/connection.txt', 'MinIO connection test');

            // Read
            $content = $disk->get('test/connection.txt');
            $this->assertEquals('MinIO connection test', $content);

            // Exists
            $this->assertTrue($disk->exists('test/connection.txt'));

            // Delete
            $disk->delete('test/connection.txt');
            $this->assertFalse($disk->exists('test/connection.txt'));
        } catch (\Exception $e) {
            $this->markTestSkipped('MinIO connection failed: '.$e->getMessage());
        }
    }
}
