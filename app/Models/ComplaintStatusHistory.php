<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ComplaintStatusHistory extends Model
{
    public $timestamps = false;

    protected $table = 'complaint_status_history';

    protected $guarded = [];
}
