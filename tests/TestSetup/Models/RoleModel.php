<?php

namespace Anil\FastApiCrud\Tests\TestSetup\Models;

use Anil\FastApiCrud\Traits\HasDateFilters;
use Anil\FastApiCrud\Traits\HasDeleteEvent;
use Anil\FastApiCrud\Traits\HasReplicatesWithRelation;
use Anil\FastApiCrud\Traits\HasUuid;
use Illuminate\Database\Eloquent\Model;

class RoleModel extends Model
{
    use HasDateFilters;
    use HasDeleteEvent;
    use HasReplicatesWithRelation;
    // use HasUuid;

    protected $table = 'roles';

    protected $fillable = [
        'name',
    ];
}
