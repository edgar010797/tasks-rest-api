<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;

class Task extends Model
{
    use HasFactory;

    protected $table = 'tasks';

    protected $fillable = [
        'title',
        'description',
        'due_date',
        'created_at',
        'status_id',
        'priority_id',
        'category_id',
        'user_id',
    ];

    protected $casts = [
        'due_date' => 'datetime',
        'created_at' => 'datetime',
    ];

    public function scopeSearch($query, $search)
    {
        if (empty($search)) return $query;
        $escaped = addcslashes($search, '%_');
        return $query->where(function ($q) use ($escaped) {
            $q->whereRaw('title ILIKE ? ESCAPE ?', ['%' . $escaped . '%', '\\'])
                ->orWhereRaw('description ILIKE ? ESCAPE ?', ['%' . $escaped . '%', '\\']);
        });
    }

    public function status()
    {
        return $this->belongsTo(Status::class);
    }

    public function priority()
    {
        return $this->belongsTo(Priority::class);
    }

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
