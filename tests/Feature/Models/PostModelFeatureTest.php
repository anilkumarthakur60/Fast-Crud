<?php

use Anil\FastApiCrud\Tests\TestSetup\Models\PostModel;
use Anil\FastApiCrud\Tests\TestSetup\Models\TagModel;

/** @test*/
describe(description: 'Testing_Tag_Model_Factory', tests: function () {
    /** @test*/
    it(description: 'test_tag_model_fillable', closure: function () {
        $tag = new TagModel;
        $fillableKeys = array_keys($tag->getFillable());
        sort($fillableKeys);
        $expectedKeys = array_keys([
            'name',
            'desc',
            'status',
            'active',
        ]);
        sort($expectedKeys);
        expect($fillableKeys)
            ->toBeArray()
            ->and($fillableKeys)
            ->toBe($expectedKeys);
    });
    it(description: 'can_create_a_tag_using_factory', closure: function () {
        $tag = TagModel::factory()
            ->create(
                [
                    'name' => $inputName = 'Tag 1',
                    'desc' => $inputDesc = 'Tag 1 Description',
                    'status' => true,
                    'active' => false,
                ],
            );
        expect($tag->name)
            ->toBe(expected: $inputName)
            ->and($tag->desc)
            ->toBe(expected: $inputDesc)
            ->and($tag->status)
            ->toBe(expected: true)
            ->and($tag->active)
            ->toBe(expected: false);
        $this->assertDatabaseHas(table: 'tags', data: [
            'name' => $inputName,
            'desc' => $inputDesc,
            'status' => true,
            'active' => false,
            'deleted_at' => null,
        ]);
        expect($tag->posts)
            ->toBeEmpty()
            ->and($tag->posts()
                ->count())
            ->toBe(0);
    });
    it(description: 'can_update_a_tag_using_factory', closure: function () {
        $tag = TagModel::factory()
            ->create(
                [
                    'name' => 'Tag 1',
                    'desc' => 'Tag 1 Description',
                    'status' => 1,
                    'active' => 0,
                ],
            );
        $tag->update(
            [
                'name' => $inputName = 'Tag 2',
                'desc' => $inputDesc = 'Tag 2 Description',
                'status' => $active = 0,
                'active' => $inActive = 1,
            ],
        );
        expect($tag->name)
            ->toBe(expected: $inputName)
            ->and($tag->desc)
            ->toBe(expected: $inputDesc)
            ->and($tag->status)
            ->toBe(expected: $active)
            ->and($tag->active)
            ->toBe(expected: $inActive)
            ->and($tag->deleted_at)
            ->toBeNull();
        $this->assertDatabaseHas('tags', [
            'name' => $inputName,
            'desc' => $inputDesc,
            'status' => $active,
            'active' => $inActive,
            'deleted_at' => null,
        ]);
    });
    it(description: 'can_delete_a_tag_using_factory', closure: function () {
        $tag = TagModel::factory()
            ->create(
                [
                    'name' => $inputName = 'Tag 1',
                    'desc' => $inputDesc = 'Tag 1 Description',
                    'status' => $active = true,
                    'active' => $inActive = false,
                ]
            );
        $tag->forceDelete();
        $this->assertDatabaseMissing('tags', [
            'name' => $inputName,
            'desc' => $inputDesc,
            'status' => $active,
            'active' => $inActive,
            'deleted_at' => null,
        ]);
    });
});
describe(description: 'test_tag_controller', tests: function () {
    it(description: 'can_get_all_tags', closure: function () {
        TagModel::factory()
            ->createMany([
                [
                    'name' => 'Tag 1',
                    'desc' => 'Tag 1 Description',
                    'status' => 1,
                    'active' => 1,
                ],
                [
                    'name' => 'Tag 2',
                    'desc' => 'Tag 2 Description',
                    'status' => 0,
                    'active' => 0,
                ],
                [
                    'name' => 'Tag 3',
                    'desc' => 'Tag 3 Description',
                    'status' => 1,
                    'active' => 0,
                ],
                [
                    'name' => 'Tag 4',
                    'desc' => 'Tag 4 Description',
                    'status' => 0,
                    'active' => 1,
                ],
            ]);
        $this->get(uri: 'tags')
            ->assertOk()
            ->assertJsonCount(count: 4, key: 'data')
            ->assertJsonStructure(
                [
                    'data' => [
                        [
                            'id',
                            'name',
                            'desc',
                            'status',
                            'active',
                            'created_at',
                            'updated_at',
                            'deleted_at',
                        ],
                    ],
                    'links' => [
                        'first',
                        'last',
                        'prev',
                        'next',
                    ],
                    'meta' => [
                        'current_page',
                        'from',
                        'last_page',
                        'path',
                        'per_page',
                        'to',
                        'total',
                    ],
                ]
            );
        $this->call(method: 'get', uri: 'tags', parameters: [
            'page' => 2,
            'rowsPerPage' => 2,
        ])
            ->assertOk()
            ->assertJsonCount(count: 2, key: 'data');
        $this->getJson(uri: 'tags?filters='.json_encode([
            'queryFilter' => 'Tag 2',
            'active' => 0,
            'status' => 0,
        ]))
            ->assertOk()
            ->assertJsonCount(count: 1, key: 'data')
            ->assertJson([
                'data' => [
                    [
                        'name' => 'Tag 2',
                        'desc' => 'Tag 2 Description',
                        'status' => 0,
                        'active' => 0,
                    ],
                ],
            ]);
        $this->getJson(uri: 'tags?filters='.json_encode([
            'queryFilter' => 'Tag 1',
            'active' => 1,
            'status' => 1,
        ]))
            ->assertOk()
            ->assertJsonCount(count: 1, key: 'data')
            ->assertJson([
                'data' => [
                    [
                        'name' => 'Tag 1',
                        'desc' => 'Tag 1 Description',
                        'status' => 1,
                        'active' => 1,
                    ],
                ],
            ]);
        $this->getJson(uri: 'tags?filters='.json_encode([
            'queryFilter' => 'Tag',
        ]))
            ->assertOk()
            ->assertJsonCount(count: 4, key: 'data')
            ->assertJson([
                'data' => [
                    [
                        'name' => 'Tag 4',
                        'desc' => 'Tag 4 Description',
                        'status' => 0,
                        'active' => 1,
                    ],
                    [
                        'name' => 'Tag 3',
                        'desc' => 'Tag 3 Description',
                        'status' => 1,
                        'active' => 0,
                    ],
                    [
                        'name' => 'Tag 2',
                        'desc' => 'Tag 2 Description',
                        'status' => 0,
                        'active' => 0,
                    ],
                    [
                        'name' => 'Tag 1',
                        'desc' => 'Tag 1 Description',
                        'status' => 1,
                        'active' => 1,
                    ],
                ],
            ]);
    });
    it(description: 'can_create_a_tag_in_api', closure: function () {
        $tag = TagModel::factory()
            ->raw(
                [
                    'status' => 1,
                    'active' => 0,
                ]
            );
        $response = $this->postJson(uri: 'tags', data: $tag);
        $response->assertStatus(status: 201);
        $this->assertDatabaseHas(table: 'tags', data: [
            ...$tag,
            'status' => 1,
            'active' => 0,
            'deleted_at' => null,
        ]);
    });
    it(description: 'can_update_a_tag', closure: function () {
        $tag = TagModel::factory()
            ->create(
                [
                    'name' => 'Tag 1',
                    'desc' => 'Tag 1 Description',
                    'status' => 1,
                    'active' => 0,
                ]
            );
        $response = $this->putJson(uri: "tags/{$tag->id}", data: $data = [
            'name' => 'Tag 2',
            'desc' => 'Tag 2 Description',
            'status' => 0,
            'active' => 1,
        ]);
        $response->assertStatus(status: 200);
        $this->assertDatabaseHas('tags', [
            ...$data,
            'deleted_at' => null,
        ]);
    });
    it(description: 'can_delete_a_tag', closure: function () {
        $tag = TagModel::factory()
            ->create([
                'name' => 'Tag 1',
            ]);
        $response = test()->deleteJson(uri: "tags/{$tag->id}");
        $response->assertStatus(status: 204);
        test()->assertDatabaseHas('tags', [
            'name' => 'Tag 1',
            'deleted_at' => now(),
        ]);
        test()->assertDatabaseMissing('post_tag', [
            'tag_id' => $tag->id,
        ]);
        test()->assertSame(0, PostModel::query()
            ->count());
    });
    it(description: 'can_get_a_tag', closure: function () {
        $tag = TagModel::factory()
            ->create();
        $response = $this->get(uri: 'tags/'.$tag->id);
        $response->assertStatus(status: 200);
        $response->assertJson(['data' => ['name' => $tag->name]]);
        $response->assertJsonStructure(
            [
                'data' => [
                    'id',
                    'name',
                    'desc',
                    'status',
                    'active',
                    'created_at',
                    'updated_at',
                    'deleted_at',
                ],
            ]
        );
    });
    it(description: 'can_post_a_tag_with_posts_ids', closure: function () {
        $postIds = PostModel::factory(2)
            ->create()
            ->modelKeys();
        $tag = TagModel::factory()
            ->raw([
                'name' => 'tag1',
                'desc' => 'tag1 description',
                'status' => 1,
                'active' => 0,
            ]);
        $response = $this->postJson(uri: 'tags', data: [
            ...$tag,
            'post_ids' => $postIds,
        ]);
        $response->assertStatus(status: 201);
        test()->assertDatabaseHas(table: 'tags', data: [
            ...$tag,
            'deleted_at' => null,
        ]);
        test()->assertDatabaseHas(table: 'post_tag', data: ['tag_id' => 1, 'post_id' => 1]);
        test()->assertDatabaseHas(table: 'post_tag', data: ['tag_id' => 1, 'post_id' => 2]);
        test()->assertSame(2, TagModel::query()->find(1)->posts()->count());
    });
});
