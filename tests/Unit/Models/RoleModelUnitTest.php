<?php

namespace Tests\Unit\Models;

use Anil\FastApiCrud\Tests\TestSetup\Models\RoleModel;
use Anil\FastApiCrud\Traits\HasDateFilters;
use Anil\FastApiCrud\Traits\HasDeleteEvent;
use Anil\FastApiCrud\Traits\HasReplicatesWithRelation;

describe('RoleModelUnitTest', function () {
    it('has used traits', function () {
        expect(class_uses(RoleModel::class))->toContain(HasDateFilters::class);
        expect(class_uses(RoleModel::class))->toContain(HasDeleteEvent::class);
        expect(class_uses(RoleModel::class))->toContain(HasReplicatesWithRelation::class);
    });
});
