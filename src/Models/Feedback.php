<?php

declare(strict_types=1);

namespace Akisolu\AnonymousFeedback\Models;

use Illuminate\Database\Eloquent\Model;

class Feedback extends Model
{
    protected $table = 'feedbacks';
    protected $primaryKey = 'feedback_id';

    public $timestamps = false;

    protected $fillable = [
        'message',
        'state_id',
    ];
}