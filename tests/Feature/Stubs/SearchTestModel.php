<?php

namespace Tests\Feature\Stubs;

use App\Traits\Searchable;
use Illuminate\Database\Eloquent\Model;

class SearchTestModel extends Model
{
    use Searchable;

    protected $table = 'search_test';

    protected $fillable = ['name', 'notes'];

    public $timestamps = false;
}
