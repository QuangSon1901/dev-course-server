<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Search extends Model
{
    use HasFactory;

    protected $table = 'search';

    protected $fillable = [
        'course_id',
        'search_keyword_id',
        'create_at',
        'update_at',
    ];

    public function search_keywords()
    {
        return $this->belongsTo(SearchKeyword::class, 'search_keyword_id');
    }

    public function courses()
    {
        return $this->belongsTo(Course::class, 'course_id');
    }
}
