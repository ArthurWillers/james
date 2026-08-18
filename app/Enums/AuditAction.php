<?php

namespace App\Enums;

enum AuditAction: string
{
    case Created = 'created';
    case Updated = 'updated';
    case Deleted = 'deleted';
    case ItemDeleted = 'item_deleted';
    case Restored = 'restored';
    case ForceDeleted = 'forceDeleted';
}
