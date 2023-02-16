<?php

declare(strict_types=1);

namespace Korridor\LaravelComputedAttributes\Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Config;
use Korridor\LaravelComputedAttributes\Tests\TestCase;
use Korridor\LaravelComputedAttributes\Tests\TestEnvironment\Models\Post;
use Korridor\LaravelComputedAttributes\Tests\TestEnvironment\Models\Vote;

class ValidateComputedAttributesCommandTest extends TestCase
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
        $this->artisan('computed-attributes:validate', [
            'modelsAttributes' => null,
        ])
            ->expectsOutput('Start validating following attributes of model ' .
                '"Korridor\LaravelComputedAttributes\Tests\TestEnvironment\Models\Post":')
            ->expectsOutput('[complex_calculation,sum_of_votes]')
            ->expectsOutput('Korridor\LaravelComputedAttributes\Tests\TestEnvironment\Models\Post[id=1][complex_calculation]')
            ->expectsOutput('Current value: null')
            ->expectsOutput('Calculated value: integer(3)')
            ->expectsOutput('Korridor\LaravelComputedAttributes\Tests\TestEnvironment\Models\Post[id=1][sum_of_votes]')
            ->expectsOutput('Current value: integer(0)')
            ->expectsOutput('Calculated value: integer(4)')
            ->assertExitCode(0)
            ->execute();

        // Assert
        $this->assertDatabaseHas('posts', [
            'id' => $post->id,
            'complex_calculation' => null,
            'sum_of_votes' => 0,
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
        $this->artisan('computed-attributes:validate', [
            'modelsAttributes' => 'Post:sum_of_votes',
        ])
            ->expectsOutput('Start validating following attributes of model ' .
                '"Korridor\LaravelComputedAttributes\Tests\TestEnvironment\Models\Post":')
            ->expectsOutput('[sum_of_votes]')
            ->expectsOutput('Korridor\LaravelComputedAttributes\Tests\TestEnvironment\Models\Post[id=1][sum_of_votes]')
            ->expectsOutput('Current value: integer(0)')
            ->expectsOutput('Calculated value: integer(4)')
            ->assertExitCode(0)
            ->execute();

        // Assert
        $this->assertDatabaseHas('posts', [
            'id' => $post->id,
            'complex_calculation' => null,
            'sum_of_votes' => 0,
        ]);
    }

    public function testNonNumericChunkSizeIsReturnsErrorMessage(): void
    {
        $this->artisan('computed-attributes:validate', [
            '--chunkSize' => 'text',
        ])
            ->expectsOutput('Option chunkSize needs to be an integer greater than zero')
            ->assertExitCode(1)
            ->execute();
    }

    public function testNegativeChunkSizeReturnsErrorMessage(): void
    {
        $this->artisan('computed-attributes:validate', [
            '--chunkSize' => '-10',
        ])
            ->expectsOutput('Option chunkSize needs to be an integer greater than zero')
            ->assertExitCode(1)
            ->execute();
    }

    public function testZeroAsChunkSizeReturnsErrorMessage(): void
    {
        $this->artisan('computed-attributes:validate', [
            '--chunkSize' => '0',
        ])
            ->expectsOutput('Option chunkSize needs to be greater than zero')
            ->assertExitCode(1)
            ->execute();
    }
}
