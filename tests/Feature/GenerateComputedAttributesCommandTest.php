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

    public function testCommandComputesAttributesForAllModelsWithTraitAndAllThereAttributes(): void
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
        Config::set(
            'computed-attributes.model_namespace',
            'Korridor\\LaravelComputedAttributes\\Tests\\TestEnvironment\\Models'
        );
        $this->assertDatabaseHas('posts', [
            'id' => $post->id,
            'complex_calculation' => null,
            'sum_of_votes' => 0,
        ]);

        // Act
        $this->artisan('computed-attributes:generate', [
            'modelsAttributes' => null,
        ])
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

    public function testCommandCanOnlyCalculateOneAttributeOfOneModelIfSpecifiedInArgument(): void
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
        Config::set(
            'computed-attributes.model_namespace',
            'Korridor\\LaravelComputedAttributes\\Tests\\TestEnvironment\\Models'
        );
        $this->assertDatabaseHas('posts', [
            'id' => $post->id,
            'complex_calculation' => null,
            'sum_of_votes' => 0,
        ]);

        // Act
        $this->artisan('computed-attributes:generate', [
            'modelsAttributes' => 'Post:sum_of_votes',
        ])
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

    public function testNonNumericChunkSizeIsReturnsErrorMessage(): void
    {
        $this->artisan('computed-attributes:generate', [
            '--chunkSize' => 'text',
        ])
            ->expectsOutput('Option chunkSize needs to be an integer greater than zero')
            ->assertExitCode(1)
            ->execute();
    }

    public function testNegativeChunkSizeReturnsErrorMessage(): void
    {
        $this->artisan('computed-attributes:generate', [
            '--chunkSize' => '-10',
        ])
            ->expectsOutput('Option chunkSize needs to be an integer greater than zero')
            ->assertExitCode(1)
            ->execute();
    }

    public function testZeroAsChunkSizeReturnsErrorMessage(): void
    {
        $this->artisan('computed-attributes:generate', [
            '--chunkSize' => '0',
        ])
            ->expectsOutput('Option chunkSize needs to be greater than zero')
            ->assertExitCode(1)
            ->execute();
    }
}
