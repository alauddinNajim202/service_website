<?php

use Illuminate\Support\Facades\Route;

use Spatie\Sitemap\Sitemap;
use Spatie\Sitemap\Tags\Url;
use App\Models\Post;

Route::get('/sitemap.xml', function () {

    $sitemap = Sitemap::create();

    $sitemap->add(Url::create('/'));
    $sitemap->add(Url::create('/about'));
    $sitemap->add(Url::create('/contact'));
    
    $posts = Post::all();

    foreach ($posts as $post) {
        $sitemap->add(
            Url::create("/post/{$post->slug}")
                ->setLastModificationDate($post->updated_at)
        );
    }

    return $sitemap;
});