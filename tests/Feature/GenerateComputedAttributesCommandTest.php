<?php

namespace Korridor\LaravelComputedAttributes\Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Config;
use Korridor\LaravelComputedAttributes\Tests\TestCase;
use Korridor\LaravelComputedAttributes\Tests\TestEnvironment\Models\Post;
use Korridor\LaravelComputedAttributes\Tests\TestEnvironment\Models\Vote;

class GenerateComputedAttributesCommandTest extends TestCase
{
    use RefreshDatabase;

    public function testCommandComputesAttributesForAllModelsWithTraitAndAllThereAttributes()
    {
        // Arrange
        $post = new Post();
        $post->title = 'titleTest';
        $post->content = 'Text';
        $post->save();
        $vote = new Vote();
        $vote->rating = 4;
        $vote->post()->associate($post);
        $vote->save();
        Config::set('computed-attributes.model_path', 'Models');
        Config::set('computed-attributes.model_namespace', 'Korridor\\LaravelComputedAttributes\\Tests\\TestEnvironment\\Models');
        $this->assertDatabaseHas('posts', [
            'id' => $post->id,
            'complex_calculation' => null,
            'sum_of_votes' => 0,
        ]);

        // Act
        $this->artisan('computed-attributes:generate --chunkSize=100')
            ->expectsOutput('Start calculating for following attributes of model '.
                '"Korridor\LaravelComputedAttributes\Tests\TestEnvironment\Models\Post":')
            ->expectsOutput('[complex_calculation,sum_of_votes]')
            ->assertExitCode(0)
            ->execute();

        // Assert
        $this->assertDatabaseHas('posts', [
            'id' => $post->id,
            'complex_calculation' => 3,
            'sum_of_votes' => 4,
        ]);
    }

    public function testCommandCanOnlyCalculateOneAttributeOfOneModelIfSpecifiedInArgument()
    {
        // Arrange
        $post = new Post();
        $post->title = 'titleTest';
        $post->content = 'Text';
        $post->save();
        $vote = new Vote();
        $vote->rating = 4;
        $vote->post()->associate($post);
        $vote->save();
        Config::set('computed-attributes.model_path', 'Models');
        Config::set('computed-attributes.model_namespace', 'Korridor\\LaravelComputedAttributes\\Tests\\TestEnvironment\\Models');
        $this->assertDatabaseHas('posts', [
            'id' => $post->id,
            'complex_calculation' => null,
            'sum_of_votes' => 0,
        ]);

        // Act
        $this->artisan('computed-attributes:generate "Post:sum_of_votes" --chunkSize=100')
            ->expectsOutput('Start calculating for following attributes of model '.
                '"Korridor\LaravelComputedAttributes\Tests\TestEnvironment\Models\Post":')
            ->expectsOutput('[sum_of_votes]')
            ->assertExitCode(0)
            ->execute();

        // Assert
        $this->assertDatabaseHas('posts', [
            'id' => $post->id,
            'complex_calculation' => null,
            'sum_of_votes' => 4,
        ]);
    }
}
