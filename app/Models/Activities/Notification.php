<?php

namespace App\Models\Activities;

use App\Traits\Activities\Activityable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Notification extends Model
{
    use HasFactory, Activityable;

    protected $table = 'activity_notifications';

    public $timestamps = false;
}
