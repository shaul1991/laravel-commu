<?php

declare(strict_types=1);

namespace App\Providers;

use App\Application\Contracts\SyncArticleTagsInterface;
use App\Domain\Core\Article\Repositories\ArticleRepositoryInterface;
use App\Domain\Core\Tag\Repositories\TagRepositoryInterface;
use App\Domain\Core\User\Repositories\UserRepositoryInterface;
use App\Domain\Core\User\Services\PasswordHasherInterface;
use App\Infrastructure\Persistence\Eloquent\EloquentArticleRepository;
use App\Infrastructure\Persistence\Eloquent\EloquentTagRepository;
use App\Infrastructure\Persistence\Eloquent\EloquentUserRepository;
use App\Infrastructure\Services\BcryptPasswordHasher;
use App\Infrastructure\Services\EloquentSyncArticleTagsService;
use App\Infrastructure\Services\MarkdownParserInterface;
use App\Infrastructure\Services\MermaidMarkdownParser;
use Illuminate\Support\ServiceProvider;

class RepositoryServiceProvider extends ServiceProvider
{
    /**
     * @var array<class-string, class-string>
     */
    public array $bindings = [
        UserRepositoryInterface::class => EloquentUserRepository::class,
        PasswordHasherInterface::class => BcryptPasswordHasher::class,
        ArticleRepositoryInterface::class => EloquentArticleRepository::class,
        TagRepositoryInterface::class => EloquentTagRepository::class,
        MarkdownParserInterface::class => MermaidMarkdownParser::class,
        SyncArticleTagsInterface::class => EloquentSyncArticleTagsService::class,
    ];

    public function register(): void
    {
        foreach ($this->bindings as $abstract => $concrete) {
            $this->app->bind($abstract, $concrete);
        }
    }

    public function boot(): void
    {
        //
    }
}
