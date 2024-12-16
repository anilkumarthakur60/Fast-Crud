<?php

use Anil\FastApiCrud\Tests\TestSetup\Models\TagModel;

function createTag(array $attributes = []): TagModel
{
    return TagModel::factory()->create($attributes);
}

function assertTagInDatabase(array $attributes): void
{
    // Use a closure to access $this in the context of the test
    test()->assertDatabaseHas('tags', [...$attributes, 'deleted_at' => null]);
}

describe('tag_model_api', function () {
    describe('crud_operations', function () {
        it('creates_a_tag', function () {
            $attributes = [
                'name'   => 'Tag 1',
                'desc'   => 'Tag 1 Description',
                'status' => 1,
                'active' => 0,
            ];

            $tag = createTag($attributes);

            expect($tag->only(['name', 'desc', 'status', 'active']))->toMatchArray($attributes);
            assertTagInDatabase($attributes);
        });

        it('updates_a_tag', function () {
            $tag = createTag([
                'name'   => 'Tag 1',
                'desc'   => 'Tag 1 Description',
                'status' => 1,
                'active' => 0,
            ]);

            $updatedAttributes = [
                'name'   => 'Tag 2',
                'desc'   => 'Tag 2 Description',
                'status' => 0,
                'active' => 1,
            ];

            $tag->update($updatedAttributes);

            expect($tag->only(['name', 'desc', 'status', 'active']))->toMatchArray($updatedAttributes);
            assertTagInDatabase($updatedAttributes);
        });

        it('soft_deletes_a_tag', function () {
            $tag = createTag(['name' => 'Tag 1']);
            $tag->delete();

            test()->assertSoftDeleted('tags', ['id' => $tag->id]);
        });

        it('force_deletes_a_tag', function () {
            $tag = createTag(['name' => 'Tag 1']);
            $tag->forceDelete();

            test()->assertDatabaseMissing('tags', ['id' => $tag->id]);
        });
    });

    describe('tag_api_endpoints', function () {
        it('retrieves_all_tags', function () {
            TagModel::factory()->createMany([
                ['name' => 'Tag 1', 'desc' => 'Tag 1 Description', 'status' => 1, 'active' => 1],
                ['name' => 'Tag 2', 'desc' => 'Tag 2 Description', 'status' => 0, 'active' => 0],
            ]);

            test()->get('tags')
                ->assertOk()
                ->assertJsonCount(2, 'data')
                ->assertJsonStructure(['data', 'links', 'meta']);
        });

        it('filters_tags_by_query', function () {
            createTag(['name' => 'Tag 1', 'status' => 1, 'active' => 1]);
            createTag(['name' => 'Tag 2', 'status' => 0, 'active' => 0]);

            $response = test()->get('tags?filters='.json_encode(['queryFilter' => 'Tag 2']));

            $response
                ->assertOk()
                ->assertJsonCount(1, 'data')
                ->assertJsonFragment(['name' => 'Tag 2']);
        });

        it('creates_a_tag_via_api', function () {
            $attributes = [
                'name'   => 'Tag 1',
                'desc'   => 'Tag 1 Description',
                'status' => 1,
                'active' => 0,
            ];

            test()->postJson('tags', $attributes)
                ->assertCreated();

            assertTagInDatabase($attributes);
        });

        it('validates_tag_name_during_creation', function () {
            $longName = str_repeat('A', 256);

            test()->postJson('tags', ['name' => $longName])
                ->assertUnprocessable()
                ->assertJsonValidationErrors(['name']);
        });

        it('updates_a_tag_via_api', function () {
            $tag = createTag([
                'name'   => 'Tag 1',
                'desc'   => 'Tag 1 Description',
                'status' => 1,
                'active' => 0,
            ]);

            $updatedAttributes = [
                'name'   => 'Tag 2',
                'desc'   => 'Tag 2 Description',
                'status' => 0,
                'active' => 1,
            ];

            test()->putJson("tags/{$tag->id}", $updatedAttributes)
                ->assertOk();

            assertTagInDatabase($updatedAttributes);
        });

        it('deletes_a_tag_via_api', function () {
            $tag = createTag(['name' => 'Tag 1']);

            test()->deleteJson("tags/{$tag->id}")
                ->assertNoContent();

            test()->assertSoftDeleted('tags', ['id' => $tag->id]);
        });
    });
});
