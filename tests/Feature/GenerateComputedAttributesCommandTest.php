<?php

declare(strict_types=1);

namespace Korridor\LaravelComputedAttributes\Tests\Feature;

use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Event;
use Illuminate\Testing\PendingCommand;
use Korridor\LaravelComputedAttributes\Tests\TestCase;
use Korridor\LaravelComputedAttributes\Tests\TestEnvironment\Events\PostSaved;
use Korridor\LaravelComputedAttributes\Tests\TestEnvironment\Events\PostSaving;
use Korridor\LaravelComputedAttributes\Tests\TestEnvironment\Models\Post;
use Korridor\LaravelComputedAttributes\Tests\TestEnvironment\Models\Vote;

class GenerateComputedAttributesCommandTest extends TestCase
{
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
        Event::fake();

        // Act
        /** @var PendingCommand $command */
        $command = $this->artisan('computed-attributes:generate', [
            'modelsAttributes' => null,
        ]);

        // Assert
        $command->expectsOutput('Start calculating for following attributes of model ' .
            '"Korridor\LaravelComputedAttributes\Tests\TestEnvironment\Models\Post":')
            ->expectsOutput('[complex_calculation,sum_of_votes]')
            ->assertExitCode(0)
            ->execute();
        $this->assertDatabaseHas('posts', [
            'id' => $post->id,
            'complex_calculation' => 3,
            'sum_of_votes' => 4,
        ]);
        Event::assertDispatched(PostSaved::class);
        Event::assertDispatched(PostSaving::class);
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
        /** @var PendingCommand $command */
        $command = $this->artisan('computed-attributes:generate', [
            'modelsAttributes' => 'Post:sum_of_votes',
        ]);

        // Assert
        $command->expectsOutput('Start calculating for following attributes of model ' .
            '"Korridor\LaravelComputedAttributes\Tests\TestEnvironment\Models\Post":')
            ->expectsOutput('[sum_of_votes]')
            ->assertExitCode(0)
            ->execute();
        $this->assertDatabaseHas('posts', [
            'id' => $post->id,
            'complex_calculation' => null,
            'sum_of_votes' => 4,
        ]);
    }

    public function testNonNumericChunkSizeIsReturnsErrorMessage(): void
    {

        // Act
        /** @var PendingCommand $command */
        $command = $this->artisan('computed-attributes:generate', [
            '--chunkSize' => 'text',
        ]);

        // Assert
        $command->expectsOutput('Option chunkSize needs to be an integer greater than zero')
            ->assertExitCode(1)
            ->execute();
    }

    public function testNegativeChunkSizeReturnsErrorMessage(): void
    {
        // Act
        /** @var PendingCommand $command */
        $command = $this->artisan('computed-attributes:generate', [
            '--chunkSize' => '-10',
        ]);

        // Assert
        $command->expectsOutput('Option chunkSize needs to be an integer greater than zero')
            ->assertExitCode(1)
            ->execute();
    }

    public function testZeroAsChunkSizeReturnsErrorMessage(): void
    {
        // Act
        /** @var PendingCommand $command */
        $command = $this->artisan('computed-attributes:generate', [
            '--chunkSize' => '0',
        ]);

        // Assert
        $command->expectsOutput('Option chunkSize needs to be greater than zero')
            ->assertExitCode(1)
            ->execute();
    }

    public function testGenerateAttributesCommandWillNotDispatchEventsIfNotDirty(): void
    {
        // Arrange
        $post = new Post();
        $post->title = 'titleTest';
        $post->content = 'Text';
        $post->complex_calculation = 3;
        $post->save();
        Config::set('computed-attributes.model_path', 'Models');
        Config::set(
            'computed-attributes.model_namespace',
            'Korridor\\LaravelComputedAttributes\\Tests\\TestEnvironment\\Models'
        );
        $this->assertDatabaseHas('posts', [
            'id' => $post->id,
            'complex_calculation' => 3,
            'sum_of_votes' => 0,
        ]);
        Event::fake();

        // Act
        /** @var PendingCommand $command */
        $command = $this->artisan('computed-attributes:generate', [
            'modelsAttributes' => 'Post:complex_calculation',
        ]);

        // Assert
        $command->expectsOutput('Start calculating for following attributes of model ' .
            '"Korridor\LaravelComputedAttributes\Tests\TestEnvironment\Models\Post":')
            ->expectsOutput('[complex_calculation]')
            ->assertExitCode(0)
            ->execute();
        $this->assertDatabaseHas('posts', [
            'id' => $post->id,
            'complex_calculation' => 3,
            'sum_of_votes' => 0,
        ]);
        Event::assertNotDispatched(PostSaved::class);
        Event::assertNotDispatched(PostSaving::class);
    }

    public function testChunkOptionCanGenerateOnlyOneBlock(): void
    {
        // Arrange
        $post1 = new Post();
        $post1->title = 'titleTest';
        $post1->content = 'Text';
        $post1->save();
        $post2 = new Post();
        $post2->title = 'titleTest';
        $post2->content = 'Text';
        $post2->save();
        Config::set('computed-attributes.model_path', 'Models');
        Config::set(
            'computed-attributes.model_namespace',
            'Korridor\\LaravelComputedAttributes\\Tests\\TestEnvironment\\Models'
        );
        $this->assertDatabaseHas('posts', [
            'id' => $post1->id,
            'complex_calculation' => null,
        ]);
        $this->assertDatabaseHas('posts', [
            'id' => $post2->id,
            'complex_calculation' => null,
        ]);

        // Act
        /** @var PendingCommand $command */
        $command = $this->artisan('computed-attributes:generate', [
            '--chunkSize' => '1',
            '--chunk' => '0',
            'modelsAttributes' => 'Post:complex_calculation',
        ]);

        // Assert
        $command->assertExitCode(0)
            ->execute();
        $this->assertDatabaseHas('posts', [
            'id' => $post1->id,
            'complex_calculation' => 3,
        ]);
        $this->assertDatabaseHas('posts', [
            'id' => $post2->id,
            'complex_calculation' => null,
        ]);
    }
}
