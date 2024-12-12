<?php

use Anil\FastApiCrud\Tests\TestClasses\Models\PostModel;
use Anil\FastApiCrud\Tests\TestClasses\Models\TagModel;

describe(description: 'Testing_Tag_Model_Factory', tests: function () {
    it(description: 'test_tag_model_fillable', closure: function () {
        $tag = new TagModel();
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
                    'name'   => $inputName = 'Tag 1',
                    'desc'   => $inputDesc = 'Tag 1 Description',
                    'status' => 1,
                    'active' => 0,
                ],
            );
        expect($tag->name)
            ->toBe(expected: $inputName)
            ->and($tag->desc)
            ->toBe(expected: $inputDesc)
            ->and($tag->status)
            ->toBe(expected: 1)
            ->and($tag->active)
            ->toBe(expected: 0);
        $this->assertDatabaseHas(table: 'tags', data: [
            'name'       => $inputName,
            'desc'       => $inputDesc,
            'status'     => 1,
            'active'     => 0,
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
                    'name'   => 'Tag 1',
                    'desc'   => 'Tag 1 Description',
                    'status' => 1,
                    'active' => 0,
                ],
            );
        $tag->update(
            [
                'name'   => $inputName = 'Tag 2',
                'desc'   => $inputDesc = 'Tag 2 Description',
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
            'name'       => $inputName,
            'desc'       => $inputDesc,
            'status'     => $active,
            'active'     => $inActive,
            'deleted_at' => null,
        ]);
    });
    it(description: 'can_delete_a_tag_using_factory', closure: function () {
        $tag = TagModel::factory()
            ->create(
                [
                    'name'   => $inputName = 'Tag 1',
                    'desc'   => $inputDesc = 'Tag 1 Description',
                    'status' => $active = 1,
                    'active' => $inActive = 0,
                ]
            );
        $tag->forceDelete();
        $this->assertDatabaseMissing('tags', [
            'name'       => $inputName,
            'desc'       => $inputDesc,
            'status'     => $active,
            'active'     => $inActive,
            'deleted_at' => null,
        ]);
        $tag = TagModel::factory()->create([
            'name'   => $inputName1 = 'Tag 1',
            'desc'   => $inputDesc1 = 'Tag 1 Description',
            'status' => $active1 = 1,
            'active' => $inActive1 = 0,
        ]);
        $tag->delete();
        $this->assertSoftDeleted('tags', [
            'name'   => $inputName1,
            'desc'   => $inputDesc1,
            'status' => $active1,
            'active' => $inActive1,
        ]);
    });
});
describe(description: 'test_tag_controller', tests: function () {
    it(description: 'can_get_all_tags_in_api', closure: function () {
        TagModel::factory()
            ->createMany([
                [
                    'name'   => 'Tag 1',
                    'desc'   => 'Tag 1 Description',
                    'status' => 1,
                    'active' => 1,
                ],
                [
                    'name'   => 'Tag 2',
                    'desc'   => 'Tag 2 Description',
                    'status' => 0,
                    'active' => 0,
                ],
                [
                    'name'   => 'Tag 3',
                    'desc'   => 'Tag 3 Description',
                    'status' => 1,
                    'active' => 0,
                ],
                [
                    'name'   => 'Tag 4',
                    'desc'   => 'Tag 4 Description',
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
            'page'        => 2,
            'rowsPerPage' => 2,
        ])
            ->assertOk()
            ->assertJsonCount(count: 2, key: 'data');
        $this->call(method: 'get', uri: 'tags', parameters: [
            'filters' => json_encode([
                'queryFilter' => 'Tag 2',
                'active'      => 0,
                'status'      => 0,
            ]),
        ])
            ->assertOk()
            ->assertJsonCount(count: 1, key: 'data')
            ->assertJson([
                'data' => [
                    [
                        'name'   => 'Tag 2',
                        'desc'   => 'Tag 2 Description',
                        'status' => 0,
                        'active' => 0,
                    ],
                ],
            ]);
        $this->call(method: 'get', uri: 'tags', parameters: [
            'filters' => json_encode([
                'queryFilter' => 'Tag 1',
                'active'      => 1,
                'status'      => 1,
            ]),
        ])
            ->assertOk()
            ->assertJsonCount(count: 1, key: 'data')
            ->assertJson([
                'data' => [
                    [
                        'name'   => 'Tag 1',
                        'desc'   => 'Tag 1 Description',
                        'status' => 1,
                        'active' => 1,
                    ],
                ],
            ]);
        $this->call(method: 'get', uri: 'tags', parameters: [
            'filters' => json_encode([
                'queryFilter' => 'Tag 3',
                'status'      => 1,
                'active'      => 0,
            ]),
        ])
            ->assertOk()
            ->assertJsonCount(count: 1, key: 'data')
            ->assertJson([
                'data' => [
                    [
                        'name'   => 'Tag 3',
                        'desc'   => 'Tag 3 Description',
                        'status' => 1,
                        'active' => 0,
                    ],
                ],
            ]);
        $this->call(method: 'get', uri: 'tags', parameters: [
            'filters' => json_encode([
                'queryFilter' => 'Tag',
            ]),
        ])
            ->assertOk()
            ->assertJsonCount(count: 4, key: 'data')
            ->assertJson([
                'data' => [
                    [
                        'name'   => 'Tag 4',
                        'desc'   => 'Tag 4 Description',
                        'status' => 0,
                        'active' => 1,
                    ],
                    [
                        'name'   => 'Tag 3',
                        'desc'   => 'Tag 3 Description',
                        'status' => 1,
                        'active' => 0,
                    ],
                    [
                        'name'   => 'Tag 2',
                        'desc'   => 'Tag 2 Description',
                        'status' => 0,
                        'active' => 0,
                    ],
                    [
                        'name'   => 'Tag 1',
                        'desc'   => 'Tag 1 Description',
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
        $this->postJson(uri: 'tags', data: $tag)
            ->assertCreated()
            ->assertStatus(status: 201);
        $this->assertDatabaseHas(table: 'tags', data: [
            ...$tag,
            'status'     => 1,
            'active'     => 0,
            'deleted_at' => null,
        ]);
        $tag = TagModel::factory()
            ->raw([
                'name' => "Lorem Ipsum is simply dummy text of the printing and typesetting industry. Lorem Ipsum has been the industry's standard dummy text ever since the 1500s, when an unknown printer took a galley of type and scrambled it to make a type specimen book. It has survived not only five centuries, but also the leap into electronic typesetting, remaining essentially unchanged. It was popularised in the 1960s with the release of Letraset sheets containing Lorem Ipsum passages, and more recently with desktop publishing software like Aldus PageMaker including versions of Lorem Ipsum.",
            ]);
        $this->postJson(uri: 'tags', data: $tag)
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['name'])
            ->assertJsonStructure(
                [
                    'message',
                    'errors' => [
                        'name',
                    ],
                ]
            );
    });
    it(description: 'can_update_a_tag_in_api', closure: function () {
        $tag = TagModel::factory()
            ->create(
                [
                    'name'   => 'Tag 1',
                    'desc'   => 'Tag 1 Description',
                    'status' => 1,
                    'active' => 0,
                ]
            );
        $this->putJson(uri: "tags/{$tag->id}", data: $data = [
            'name'   => 'Tag 2',
            'desc'   => 'Tag 2 Description',
            'status' => 0,
            'active' => 1,
        ])
            ->assertOk();
        $this->assertDatabaseHas('tags', [
            ...$data,
            'deleted_at' => null,
        ]);
    });
    it(description: 'can_delete_a_tag_in_api', closure: function () {
        $tag = TagModel::factory()
            ->create([
                'name' => 'Tag 1',
            ]);
        $this->deleteJson(uri: "tags/{$tag->id}")
            ->assertNoContent();
        $this->assertDatabaseHas('tags', [
            'name'       => 'Tag 1',
            'deleted_at' => now(),
        ]);
        $this->assertDatabaseMissing('post_tag', [
            'tag_id' => $tag->id,
        ]);
        $this->assertSame(0, TagModel::query()
            ->count());
    });
    it(description: 'can_post_a_tag_with_posts_ids_in_api', closure: function () {
        $postIds = PostModel::factory(2)
            ->create()
            ->modelKeys();
        $tag = TagModel::factory()
            ->raw([
                'name'   => 'tag1',
                'desc'   => 'tag1 description',
                'status' => 1,
                'active' => 0,
            ]);
        $this->postJson(uri: 'tags', data: [
            ...$tag,
            'post_ids' => $postIds,
        ])
            ->assertCreated();
        $this->assertDatabaseHas(table: 'tags', data: [
            ...$tag,
            'deleted_at' => null,
        ]);
        $this->assertDatabaseHas(table: 'post_tag', data: ['tag_id' => 1, 'post_id' => 1]);
        $this->assertDatabaseHas(table: 'post_tag', data: ['tag_id' => 1, 'post_id' => 2]);
        $this->assertSame(2, TagModel::query()->find(1)->posts()->count());
    });
    it(description: 'can_delete_multiple_tags_in_api', closure: function () {
        $tags = TagModel::factory(3)
            ->create();
        $this->postJson(uri: 'tags/delete', data: ['delete_rows' => $tags->modelKeys()])
            ->assertNoContent();
        $this->assertSoftDeleted('tags', ['id' => 1]);
        $this->assertSoftDeleted('tags', ['id' => 2]);
        $this->assertSoftDeleted('tags', ['id' => 3]);
    });
    it(description: 'can_restore_all_trashed_tags_in_api', closure: function () {
        TagModel::factory(3)
            ->trashed()
            ->create();
        $this->postJson(uri: 'tags/restore-all-trashed')
            ->assertOk();
        $this->assertDatabaseHas('tags', ['id' => 1, 'deleted_at' => null]);
        $this->assertDatabaseHas('tags', ['id' => 2, 'deleted_at' => null]);
        $this->assertDatabaseHas('tags', ['id' => 3, 'deleted_at' => null]);
    });
    it(description: 'can_force_delete_trashed_tags_in_api', closure: function () {
        TagModel::factory(3)
            ->trashed()
            ->create();
        $this->deleteJson(uri: 'tags/force-delete-trashed/1')
            ->assertNoContent();
        $this->assertDatabaseMissing('tags', ['id' => 1]);
        $this->assertSame(0, TagModel::query()->count());
    });
    it(description: 'can_restore_trashed_tag_in_api', closure: function () {
        $tag = TagModel::factory()
            ->trashed()
            ->create();
        $this->putJson(uri: "tags/{$tag->id}/restore-trashed")
            ->assertOk();
        $this->assertDatabaseHas('tags', ['id' => $tag->id, 'deleted_at' => null]);
    });
    it(description: 'can_status_change_tag_in_api', closure: function () {
        $tag = TagModel::factory()
            ->create([
                'status' => 1,
            ]);
        $this->putJson(uri: "tags/{$tag->id}/status-change")
            ->assertOk();
        $this->assertDatabaseHas('tags', ['id' => $tag->id, 'status' => 0]);
        $this->putJson(uri: "tags/{$tag->id}/status-change")
            ->assertOk();
        $this->assertDatabaseHas('tags', ['id' => $tag->id, 'status' => 1]);
    });
    it(description: 'can_status_change_other_column_tag_in_api', closure: function () {
        $tag = TagModel::factory()
            ->create([
                'active' => 1,
            ]);
        $this->putJson(uri: "tags/{$tag->id}/status-change/active")
            ->assertOk();
        $this->assertDatabaseHas('tags', ['id' => $tag->id, 'active' => 0]);
        $this->putJson(uri: "tags/{$tag->id}/status-change/active")
            ->assertOk();
        $this->assertDatabaseHas('tags', ['id' => $tag->id, 'active' => 1]);
    });
});
