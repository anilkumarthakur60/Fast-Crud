<?php

use Anil\FastApiCrud\Tests\TestSetup\Models\UserModel;

describe(description: 'User Model Feature Test', tests: function () {
    it('can create a user', function () {
        $userData = [
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'password' => Hash::make('password123'),
            'active' => true,
            'status' => true,
        ];

        $user = UserModel::create($userData);

        expect($user)->toBeInstanceOf(UserModel::class)
            ->and($user->name)->toBe('John Doe')
            ->and(Hash::check('password123', $user->password))->toBeTrue()
            ->and($user->active)->toBeTrue()
            ->and($user->status)->toBeTrue();

        $this->assertDatabaseHas('users', [
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'active' => true,
            'status' => true,
        ]);
    });

    it(description: 'it should have all the fillable fields', closure: function () {
        $tag = new UserModel;
        $fillables = $tag->getFillable();
        sort($fillables);
        $expectedFillables = [
            'name',
            'email',
            'password',
            'status',
            'active',
        ];
        sort($expectedFillables);
        expect($fillables)
            ->toBeArray()
            ->and($fillables)
            ->toBe($expectedFillables);
    });
    it(description: 'can_create_a_user_using_factory', closure: function () {
        $user = UserModel::factory()
            ->create(
                [
                    'name' => $inputName = 'John Doe',
                    'email' => $inputEmail = 'john@example.com',
                    'password' => $inputPassword = 'password123',
                    'status' => true,
                    'active' => false,
                ],
            );
        expect($user->name)
            ->toBe(expected: $inputName)
            ->and($user->email)
            ->toBe(expected: $inputEmail)
            ->and($user->password)
            ->toBe(expected: $inputPassword)
            ->and($user->status)
            ->toBe(expected: true)
            ->and($user->active)
            ->toBe(expected: false);
        $this->assertDatabaseHas(table: 'users', data: [
            'name' => $inputName,
            'email' => $inputEmail,
            'password' => $inputPassword,
            'status' => true,
            'active' => false,
        ]);
        expect($user->posts)
            ->toBeEmpty()
            ->and($user->posts()
                ->count())
            ->toBe(0);
    });
    it(description: 'can_update_a_user_using_factory', closure: function () {
        $user = UserModel::factory()
            ->create(
                [
                    'name' => 'John Doe',
                    'email' => 'john@example.com',
                    'password' => 'password123',
                    'status' => 1,
                    'active' => 0,
                ],
            );
        $user->update(
            [
                'name' => $inputName = 'Jane Doe',
                'email' => $inputEmail = 'jane@example.com',
                'password' => $inputPassword = 'password123',
                'status' => $active = 0,
                'active' => $inActive = 1,
            ],
        );
        expect($user->name)
            ->toBe(expected: $inputName)
            ->and($user->email)
            ->toBe(expected: $inputEmail)
            ->and($user->password)
            ->toBe(expected: $inputPassword)
            ->and($user->status)
            ->toBe(expected: $active)
            ->and($user->active)
            ->toBe(expected: $inActive);
        $this->assertDatabaseHas('users', [
            'name' => $inputName,
            'email' => $inputEmail,
            'password' => $inputPassword,
            'status' => $active,
            'active' => $inActive,
        ]);
    });
    it(description: 'can_delete_a_user_using_factory', closure: function () {
        $user = UserModel::factory()
            ->create(
                [
                    'name' => $inputName = 'John Doe',
                    'email' => $inputEmail = 'john@example.com',
                    'password' => $inputPassword = 'password123',
                    'status' => $active = true,
                    'active' => $inActive = false,
                ]
            );
        $user->forceDelete();
        $this->assertDatabaseMissing('users', [
            'name' => $inputName,
            'email' => $inputEmail,
            'password' => $inputPassword,
            'status' => $active,
            'active' => $inActive,
        ]);
    });
});
describe(description: 'User Model API Test', tests: function () {
    it(description: 'can_get_all_users', closure: function () {
        UserModel::factory()
            ->createMany([
                [
                    'name' => 'John Doe1',
                    'email' => 'john1@example.com',
                    'password' => 'password123',
                    'status' => 1,
                    'active' => 1,
                ],
                [
                    'name' => 'Jane Doe2',
                    'email' => 'jane2@example.com',
                    'password' => 'password123',
                    'status' => 0,
                    'active' => 0,
                ],
                [
                    'name' => 'Jane Doe3',
                    'email' => 'jane3@example.com',
                    'password' => 'password123',
                    'status' => 1,
                    'active' => 0,
                ],
                [
                    'name' => 'Jane Doe4',
                    'email' => 'jane4@example.com',
                    'password' => 'password123',
                    'status' => 0,
                    'active' => 1,
                ],
                [
                    'name' => 'John Doe5',
                    'email' => 'john5@example.com',
                    'password' => 'password123',
                    'status' => 1,
                    'active' => 1,
                ],
                [
                    'name' => 'Jane Doe6',
                    'email' => 'jane6@example.com',
                    'password' => 'password123',
                    'status' => 0,
                    'active' => 0,
                ],
                [
                    'name' => 'Jane Doe7',
                    'email' => 'jane7@example.com',
                    'password' => 'password123',
                    'status' => 1,
                    'active' => 0,
                ],
                [
                    'name' => 'Jane Doe8',
                    'email' => 'jane8@example.com',
                    'password' => 'password123',
                    'status' => 0,
                    'active' => 1,
                ],
                [
                    'name' => 'Jane Doe9',
                    'email' => 'jane9@example.com',
                    'password' => 'password123',
                    'status' => 1,
                    'active' => 1,
                ],
                [
                    'name' => 'Jane Doe10',
                    'email' => 'jane10@example.com',
                    'password' => 'password123',
                    'status' => 0,
                    'active' => 0,
                ],
            ]);
        $this->get(uri: route('users.index', [
            'page' => 1,
            'rowsPerPage' => 10,
        ]))
            ->assertOk()
            ->assertJsonCount(count: 10, key: 'data')
            ->assertJsonStructure(
                [
                    'data' => [
                        [
                            'id',
                            'name',
                            'email',
                            'status',
                            'active',
                            'created_at',
                            'updated_at',
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

        $this->getJson(uri: route('users.index', [
            'page' => 1,
            'rowsPerPage' => 2,
            'filters' => json_encode([
                'active' => 0,
                'status' => 0,
            ]),
        ]))
            ->assertOk()
            ->assertJsonCount(count: 2, key: 'data')
            ->assertJsonStructure([
                'data' => [
                    '*' => [
                        'name',
                        'email',
                        'status',
                        'active',
                        'created_at',
                        'updated_at',
                    ],
                ],
            ])
            ->assertJson([
                'data' => [
                    [
                        'name' => 'Jane Doe10',
                        'email' => 'jane10@example.com',
                        'status' => 0,
                        'active' => 0,
                        'created_at' => now()->format('Y-m-d H:i'),
                        'updated_at' => now()->format('Y-m-d H:i'),
                    ],
                    [
                        'name' => 'Jane Doe6',
                        'email' => 'jane6@example.com',
                        'status' => 0,
                        'active' => 0,
                        'created_at' => now()->format('Y-m-d H:i'),
                        'updated_at' => now()->format('Y-m-d H:i'),
                    ],
                ],
            ]);

        $this->getJson(uri: route('users.index', [
            'page' => 1,
            'rowsPerPage' => 2,
            'filters' => json_encode([
                'queryFilter' => 'Jane Doe10',
                'active' => 0,
                'status' => 0,
            ]),
        ]))
            ->assertOk()
            ->assertJsonCount(count: 1, key: 'data')
            ->assertJsonStructure([
                'data' => [
                    '*' => [
                        'name',
                        'email',
                        'status',
                        'active',
                        'created_at',
                        'updated_at',
                    ],
                ],
            ]);

        dd(
            $this->getJson(uri: route('users.index', [
                'page' => 1,
                'rowsPerPage' => 2,
                'filters' => json_encode([
                    'queryFilter' => 'Jane Doe10',
                    'active' => 0,
                    'status' => 0,
                ]),
            ]))->json()
        );

        $this->getJson(uri: route('users.index', [
            'page' => 2,
            'rowsPerPage' => 5,
            'filters' => json_encode([
                'queryFilter' => 'Jane Doe3',
                'active' => 0,
                'status' => 0,
            ]),
        ]))
            ->assertOk()
            ->assertJsonCount(count: 1, key: 'data')
            ->assertJson([
                'data' => [
                    [
                        'name' => 'Jane Doe4',
                        'email' => 'jane4@example.com',
                        'status' => 0,
                        'active' => 1,
                    ],
                ],
            ]);
        $this->getJson(uri: route('users.index', [
            'filters' => json_encode([
                'queryFilter' => 'Jane Doe4',
                'active' => 1,
                'status' => 1,
            ]),
        ]))
            ->assertOk()
            ->assertJsonCount(count: 1, key: 'data')
            ->assertJson([
                'data' => [
                    [
                        'name' => 'John Doe1',
                        'email' => 'john1@example.com',
                        'status' => 1,
                        'active' => 1,
                    ],
                ],
            ]);
        $this->getJson(uri: route('users.index', [
            'filters' => json_encode([
                'queryFilter' => 'John Doe1',
            ]),
        ]))
            ->assertOk()
            ->assertJsonCount(count: 4, key: 'data')
            ->assertJson([
                'data' => [
                    [
                        'name' => 'John Doe1',
                        'email' => 'john1@example.com',
                        'status' => 1,
                        'active' => 1,
                    ],
                    [
                        'name' => 'Jane Doe3',
                        'email' => 'jane3@example.com',
                        'status' => 1,
                        'active' => 0,
                    ],
                    [
                        'name' => 'Jane Doe2',
                        'email' => 'jane2@example.com',
                        'status' => 0,
                        'active' => 0,
                    ],
                    [
                        'name' => 'John Doe1',
                        'email' => 'john1@example.com',
                        'status' => 1,
                        'active' => 1,
                    ],
                ],
            ]);
    });
    it(description: 'can_create_a_user_in_api', closure: function () {
        $user = UserModel::factory()
            ->raw(
                [
                    'status' => 1,
                    'active' => 0,
                ]
            );
        $response = $this->postJson(uri: route('users.store'), data: $user);
        $response->assertStatus(status: 201);
        $this->assertDatabaseHas(table: 'users', data: [
            ...$user,
            'status' => 1,
            'active' => 0,
            'deleted_at' => null,
        ]);
    });
    it(description: 'can_update_a_user', closure: function () {
        $user = UserModel::factory()
            ->create(
                [
                    'name' => 'John Doe1',
                    'email' => 'john1@example.com',
                    'password' => 'password123',
                    'status' => 1,
                    'active' => 0,
                ]
            );
        $response = $this->putJson(uri: route('users.update', $user->id), data: $data = [
            'name' => 'Jane Doe2',
            'email' => 'jane2@example.com',
            'password' => 'password123',
            'status' => 0,
            'active' => 1,
        ]);
        $response->assertStatus(status: 200);
        $this->assertDatabaseHas('users', [
            ...$data,
            'deleted_at' => null,
        ]);
    });
    it(description: 'can_delete_a_user', closure: function () {
        $user = UserModel::factory()
            ->create([
                'name' => 'John Doe1',
                'email' => 'john1@example.com',
                'password' => 'password123',
                'status' => 1,
                'active' => 0,
            ]);
        $response = $this->deleteJson(uri: route('users.destroy', $user->id));
        $response->assertOk();
        $response->assertJsonCount(count: 0, key: 'data');
        $this->assertDatabaseHas('users', [
            'name' => 'John Doe1',
            'email' => 'john1@example.com',
            'password' => 'password123',
            'status' => 1,
            'active' => 0,
            'deleted_at' => now(),
        ]);
        $this->assertDatabaseMissing('users', [
            'name' => 'John Doe1',
            'email' => 'john1@example.com',
            'password' => 'password123',
            'status' => 1,
            'active' => 0,
        ]);
        $this->assertSame(0, UserModel::query()
            ->count());
    });
    it(description: 'can_get_a_user', closure: function () {
        $user = UserModel::factory()
            ->create();
        $response = $this->get(uri: route('users.show', $user->id));
        $response->assertStatus(status: 200);
        $response->assertJson(['data' => ['name' => $user->name]]);
        $response->assertJsonStructure(
            [
                'data' => [
                    'id',
                    'name',
                    'email',
                    'status',
                    'active',
                    'created_at',
                    'updated_at',
                    'deleted_at',
                ],
            ]
        );
    });
    it(description: 'can_post_a_user_with_posts_ids', closure: function () {
        $postIds = UserModel::factory(2)
            ->create()
            ->modelKeys();
        $user = UserModel::factory()
            ->raw([
                'name' => 'user1',
                'email' => 'user1@example.com',
                'password' => 'password123',
                'status' => 1,
                'active' => 0,
            ]);
        $response = $this->postJson(uri: route('users.store'), data: [
            ...$user,
            'post_ids' => $postIds,
        ]);
        $response->assertStatus(status: 201);
        $this->assertDatabaseHas(table: 'users', data: [
            ...$user,
            'deleted_at' => null,
        ]);
        $this->assertDatabaseHas(table: 'post_user', data: ['user_id' => 1, 'post_id' => 1]);
        $this->assertDatabaseHas(table: 'post_user', data: ['user_id' => 1, 'post_id' => 2]);
        $this->assertSame(2, UserModel::query()->find(1)->posts()->count());
    });
})->only();
