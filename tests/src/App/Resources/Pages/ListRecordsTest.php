<?php

use Filament\Tables\Actions\DeleteAction;
use Filament\Tables\Actions\DeleteBulkAction;
use Filament\Tests\App\Fixtures\Resources\PostResource;
use Filament\Tests\App\Fixtures\Resources\UserResource;
use Filament\Tests\App\Resources\TestCase;
use Filament\Tests\Models\Post;
use function Pest\Livewire\livewire;

uses(TestCase::class);

it('can render posts page', function () {
    $this->get(PostResource::getUrl('index'))->assertSuccessful();
});

it('can render users page', function () {
    $this->get(UserResource::getUrl('index'))->assertSuccessful();
});

it('can list posts', function () {
    $posts = Post::factory()->count(10)->create();

    livewire(PostResource\Pages\ListPosts::class)
        ->assertCanSeeTableRecords($posts);
});

it('can render post titles', function () {
    Post::factory()->count(10)->create();

    livewire(PostResource\Pages\ListPosts::class)
        ->assertCanRenderTableColumn('title');
});

it('can render post authors', function () {
    Post::factory()->count(10)->create();

    livewire(PostResource\Pages\ListPosts::class)
        ->assertCanRenderTableColumn('author.name');
});

it('can sort posts by title', function () {
    $posts = Post::factory()->count(10)->create();

    livewire(PostResource\Pages\ListPosts::class)
        ->sortTable('title')
        ->assertCanSeeTableRecords($posts->sortBy('title'), inOrder: true)
        ->sortTable('title', 'desc')
        ->assertCanSeeTableRecords($posts->sortByDesc('title'), inOrder: true);
});

it('can sort posts by author', function () {
    $posts = Post::factory()->count(10)->create();

    livewire(PostResource\Pages\ListPosts::class)
        ->sortTable('author.name')
        ->assertCanSeeTableRecords($posts->sortBy('author.name'), inOrder: true)
        ->sortTable('author.name', 'desc')
        ->assertCanSeeTableRecords($posts->sortByDesc('author.name'), inOrder: true);
});

it('can search posts by title', function () {
    $posts = Post::factory()->count(10)->create();

    $title = $posts->first()->title;

    livewire(PostResource\Pages\ListPosts::class)
        ->searchTable($title)
        ->assertCanSeeTableRecords($posts->where('title', $title))
        ->assertCanNotSeeTableRecords($posts->where('title', '!=', $title));
});

it('can search posts by author', function () {
    $posts = Post::factory()->count(10)->create();

    $author = $posts->first()->author->name;

    livewire(PostResource\Pages\ListPosts::class)
        ->searchTable($author)
        ->assertCanSeeTableRecords($posts->where('author.name', $author))
        ->assertCanNotSeeTableRecords($posts->where('author.name', '!=', $author));
});

it('can filter posts by `is_published`', function () {
    $posts = Post::factory()->count(10)->create();

    livewire(PostResource\Pages\ListPosts::class)
        ->assertCanSeeTableRecords($posts)
        ->filterTable('is_published')
        ->assertCanSeeTableRecords($posts->where('is_published', true))
        ->assertCanNotSeeTableRecords($posts->where('is_published', false));
});

it('can delete posts', function () {
    $post = Post::factory()->create();

    livewire(PostResource\Pages\ListPosts::class)
        ->callTableAction(DeleteAction::class, $post);

    $this->assertModelMissing($post);
});

it('can bulk delete posts', function () {
    $posts = Post::factory()->count(10)->create();

    livewire(PostResource\Pages\ListPosts::class)
        ->callTableBulkAction(DeleteBulkAction::class, $posts);

    foreach ($posts as $post) {
        $this->assertModelMissing($post);
    }
});