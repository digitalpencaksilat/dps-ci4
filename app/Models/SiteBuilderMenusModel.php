<?php

namespace App\Models;

use CodeIgniter\Model;

class SiteBuilderMenusModel extends Model
{
    protected $table            = 'site_builder_menus';
    protected $primaryKey       = 'id';
    protected $returnType       = 'object';
    protected $useSoftDeletes   = false;
    protected $protectFields    = false;

    // Read-only usage for public nav.
    protected bool $allowEmptyInserts = false;
}
