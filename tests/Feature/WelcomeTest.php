<?php

namespace Tests\Feature;

use Carbon\Carbon;
use Ellllllen\PersonalWebsite\Articles\Article;
use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class WelcomeTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @test
     */
    public function testPageLoads()
    {
        $response = $this->get('/');
        $response->assertStatus(200);
    }

    /**
     * @test
     * @throws \Throwable
     */
    public function testNoArticlesDisplaysMessage()
    {
        $response = $this->get('/');
        $response->assertSee("Sorry, I haven't posted any articles yet");
    }

    /**
     * @test
     * @throws \Throwable
     */
    public function testItDisplaysOneArticles()
    {
        factory(Article::class, 1)->create(['title' => 'Test Article']);

        $response = $this->get('/');

        $response->assertSee('Test Article')
            ->assertDontSee("Sorry, I haven't posted any articles yet");
    }

    /**
     * @test
     * @throws \Throwable
     */
    public function testItDoesNotDisplayArticlesWithoutImageFile()
    {
        factory(Article::class, 1)->create([
            'title' => 'Test Article',
            'image' => 'blahblahblah.jpg',
        ]);

        $response = $this->get('/');
        $response->assertDontSee('Test Article')
            ->assertSee("Sorry, I haven't posted any articles yet");
    }

    /**
     * @test
     * @throws \Throwable
     */
    public function testOnlyShowsLatestArticles()
    {
        for ($count = 1; $count <= 9; $count++) {
            factory(Article::class, 1)->create([
                'title' => "Test Article {$count}",
                'updated_at' => Carbon::now()->addSecond($count),
            ]);
        }

        $response = $this->get('/');
        for ($count = 1; $count <= 4; $count++) {
            $response->assertDontSee('Test Article ' . $count);
        }
        for ($count = 5; $count <= 9; $count++) {
            $response->assertSee('Test Article ' . $count);
        }
    }
}
