<?php

use Anil\FastApiCrud\Tests\TestSetup\Models\RoleModel;

describe('RoleModel', function () {
    it('can be instantiated', function () {
        $role = new RoleModel;
        expect($role->getIncrementing())->toBeFalse();
        expect($role->getKeyType())->toBeString();
    });

    it('can be created', function () {
        $role = RoleModel::create([
            'name' => 'Admin',
        ]);
        expect($role->id)->not->toBeNull();
        expect($role->name)->toBe('Admin');
    });

    it('can be updated', function () {
        $role = RoleModel::create([
            'name' => 'Admin',
        ]);
        $role->name = 'Super Admin';
        $role->save();
        expect($role->name)->toBe('Super Admin');
    });

    it('has a unique UUID', function () {
        $role1 = RoleModel::create(['name' => 'Admin']);
        $role2 = RoleModel::create(['name' => 'User']);
        expect($role1->id)->not->toBe($role2->id);
    });
});
