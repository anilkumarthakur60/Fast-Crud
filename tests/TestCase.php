<?php

namespace Anil\FastApiCrud\Tests;

use Anil\FastApiCrud\Providers\ApiCrudServiceProvider;
use Anil\FastApiCrud\Tests\TestSetup\Controllers\PostController;
use Anil\FastApiCrud\Tests\TestSetup\Controllers\TagController;
use Anil\FastApiCrud\Tests\TestSetup\Controllers\UserController;
use Anil\FastApiCrud\Tests\TestSetup\Middleware\PermissionMiddleware;
use Anil\FastApiCrud\Tests\TestSetup\Models\PermissionModel;
use Anil\FastApiCrud\Tests\TestSetup\Models\PostModel;
use Anil\FastApiCrud\Tests\TestSetup\Models\TagModel;
use Anil\FastApiCrud\Tests\TestSetup\Models\UserModel;
use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Routing\Router;
use Orchestra\Testbench\TestCase as OrchestraTestCase;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionServiceProvider;

abstract class TestCase extends OrchestraTestCase
{
    use DatabaseMigrations;

    protected Permission $testClientPermission;

    protected Role $testClientRole;

    /**
     * @throws BindingResolutionException
     */
    protected function setUp(): void
    {
        parent::setUp();

        Factory::guessFactoryNamesUsing(
            function (string $modelName): string {
                return 'Anil\FastApiCrud\\Database\\Factories\\'.class_basename($modelName).'Factory';
            }
        );
        /** @var Application $app */
        $app = $this->app;
        $app['config']->set('auth.guards.web', [
            'driver' => 'session',
            'provider' => 'users',
        ]);

        $app['config']->set('auth.providers.users', [
            'driver' => 'eloquent',
            'model' => UserModel::class,
        ]);

        $app['config']->set('auth.defaults.guard', 'web');
        $app['config']->set('permission.guard_name', 'web');
        $this->setUpDatabase();
        $this->setupMiddleware();

    }

    protected function setUpDatabase(): void
    {
        $this->userMigration();
        $this->tagMigration();
        $this->postMigration();
        $this->permissionMigration();
    }

    protected function userMigration(): void
    {
        /** @var Application $app */
        $app = $this->app;
        $app['db']->connection()
            ->getSchemaBuilder()
            ->create('users', function (Blueprint $table) {
                $table->id();
                $table->string(column: 'name');
                $table->string(column: 'email');
                $table->string(column: 'password');
                $table->boolean(column: 'active')
                    ->default(true);
                $table->boolean(column: 'status')
                    ->default(true);
                $table->timestamps();
                $table->softDeletes();
            });
    }

    protected function tagMigration(): void
    {

        /** @var Application $app */
        $app = $this->app;
        $app['db']->connection()
            ->getSchemaBuilder()
            ->create('tags', function (Blueprint $table) {
                $table->id();
                $table->string(column: 'name');
                $table->longText(column: 'desc');
                $table->boolean(column: 'status')
                    ->default(true);
                $table->boolean(column: 'active')
                    ->default(true);
                $table->timestamps();
                $table->softDeletes();
            });
    }

    protected function postMigration(): void
    {
        /** @var Application $app */
        $app = $this->app;
        $app['db']->connection()
            ->getSchemaBuilder()
            ->create('posts', function (Blueprint $table) {
                $table->id();
                $table->string(column: 'name');
                $table->longText(column: 'desc');
                $table->boolean(column: 'status')
                    ->default(true);
                $table->boolean(column: 'active')
                    ->default(true);
                $table->foreignIdFor(UserModel::class, 'user_id')
                    ->constrained('users')
                    ->cascadeOnDelete();
                $table->timestamps();
                $table->softDeletes();
            });
        $app['db']->connection()
            ->getSchemaBuilder()
            ->create('post_tag', function (Blueprint $table) {
                $table->id();
                $table->foreignIdFor(PostModel::class, 'post_id')
                    ->constrained('posts')
                    ->cascadeOnDelete();
                $table->foreignIdFor(TagModel::class, 'tag_id')
                    ->constrained('tags')
                    ->cascadeOnDelete();
            });
    }

    protected function permissionMigration(): void
    {
        /** @var Application $app */
        $app = $this->app;
        $app['db']->connection()
            ->getSchemaBuilder()
            ->create('permissions', function (Blueprint $table) {
                $table->id();
                $table->string(column: 'name');
                $table->timestamps();
            });

        $app['db']->connection()
            ->getSchemaBuilder()
            ->create('user_permission', function (Blueprint $table) {
                $table->id();
                $table->foreignIdFor(UserModel::class, 'user_id')
                    ->constrained('users')
                    ->cascadeOnDelete();
                $table->foreignIdFor(PermissionModel::class, 'permission_id')
                    ->constrained('permissions')
                    ->cascadeOnDelete();
            });
    }

    /**
     * @throws BindingResolutionException
     */
    private function setupMiddleware(): void
    {
        /** @var Application $app */
        $app = $this->app;
        $router = $app->make(Router::class);
        // $router->aliasMiddleware('role', RoleMiddleware::class);
        $router->aliasMiddleware('permission', PermissionMiddleware::class);
        // $router->aliasMiddleware('role_or_permission', RoleOrPermissionMiddleware::class);
    }

    protected function getPackageProviders($app): array
    {
        return [
            ApiCrudServiceProvider::class,
            PermissionServiceProvider::class,
        ];
    }

    /**
     * Define routes setup.
     *
     * @param  Router  $router
     */
    protected function defineRoutes($router): void
    {
        $this->postRoutes($router);
        $this->tagRoutes($router);
        $this->userRoutes($router);
    }

    private function postRoutes(Router $router): void
    {
        $router->get('posts', [PostController::class, 'index'])
            ->name('posts.index');
        $router->post('posts', [PostController::class, 'store'])
            ->name('posts.store');
        $router->post('posts/delete', [PostController::class, 'delete'])
            ->name('posts.delete');
        $router->post('posts/restore-all-trashed', [PostController::class, 'restoreAllTrashed'])
            ->name('posts.restore-all-trashed');
        $router->post('posts/force-delete-trashed', [PostController::class, 'forceDeleteTrashed'])
            ->name('posts.force-delete-trashed');
        $router->get('posts/{id}', [PostController::class, 'show'])
            ->name('posts.show');
        $router->put('posts/{id}', [PostController::class, 'update'])
            ->name('posts.update');
        $router->put('posts/{id}/status-change/{column}', [PostController::class, 'changeStatusOtherColumn'])
            ->name('posts.changeStatusOtherColumn');
        $router->put('posts/{id}/status-change', [PostController::class, 'changeStatus'])
            ->name('posts.changeStatus');
        $router->put('posts/{id}/restore-trashed', [PostController::class, 'restoreTrashed'])
            ->name('posts.restoreTrashed');
        $router->delete('posts/{id}', [PostController::class, 'destroy'])
            ->name('posts.destroy');
    }

    private function tagRoutes(Router $router): void
    {
        $router->get('tags', [TagController::class, 'index'])
            ->name('tags.index');
        $router->post('tags', [TagController::class, 'store'])
            ->name('tags.store');
        $router->post('tags/delete', [TagController::class, 'delete'])
            ->name('tags.delete');
        $router->post('tags/restore-all-trashed', [TagController::class, 'restoreAllTrashed'])
            ->name('tags.restore-all-trashed');
        $router->delete('tags/force-delete-trashed/{id}', [TagController::class, 'forceDeleteTrashed'])
            ->name('tags.force-delete-trashed');
        $router->get('tags/{id}', [TagController::class, 'show'])
            ->name('tags.show');
        $router->put('tags/{id}', [TagController::class, 'update'])
            ->name('tags.update');
        $router->put('tags/{id}/status-change/{column}', [TagController::class, 'changeStatusOtherColumn'])
            ->name('tags.changeStatusOtherColumn');
        $router->put('tags/{id}/status-change', [TagController::class, 'changeStatus'])
            ->name('tags.changeStatus');
        $router->put('tags/{id}/restore-trashed', [TagController::class, 'restoreTrashed'])
            ->name('tags.restoreTrashed');
        $router->delete('tags/{id}', [TagController::class, 'destroy'])
            ->name('tags.destroy');
    }

    //spatie permission

    private function userRoutes(Router $router): void
    {
        $router->get('users', [UserController::class, 'index'])
            ->name('users.index');
        $router->post('users', [UserController::class, 'store'])
            ->name('users.store');
        $router->post('users/delete', [UserController::class, 'delete'])
            ->name('users.delete');
        $router->post('users/restore-all-trashed', [UserController::class, 'restoreAllTrashed'])
            ->name('users.restore-all-trashed');
        $router->post('users/force-delete-trashed', [UserController::class, 'forceDeleteTrashed'])
            ->name('users.force-delete-trashed');
        $router->get('users/{id}', [UserController::class, 'show'])
            ->name('users.show');
        $router->put('users/{id}', [UserController::class, 'update'])
            ->name('users.update');
        $router->put('users/{id}/status-change/{column}', [UserController::class, 'changeStatusOtherColumn'])
            ->name('users.changeStatusOtherColumn');
        $router->put('users/{id}/status-change', [UserController::class, 'changeStatus'])
            ->name('users.changeStatus');
        $router->put('users/{id}/restore-trashed', [UserController::class, 'restoreTrashed'])
            ->name('users.restoreTrashed');
        $router->delete('users/{id}', [UserController::class, 'destroy'])
            ->name('users.destroy');
    }
}
