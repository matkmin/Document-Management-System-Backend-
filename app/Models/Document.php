<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Document extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'description',
        'file_name',
        'file_path',
        'file_type',
        'file_size',
        'document_category_id',
        'department_id',
        'uploaded_by',
        'access_level',
        'download_count',
    ];

    public function uploader()
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }

    public function department()
    {
        return $this->belongsTo(Department::class);
    }

    public function category()
    {
        return $this->belongsTo(DocumentCategory::class, 'document_category_id');
    }

    public function scopeAccessibleBy(Builder $query, User $user)
    {
        if ($user->isAdmin()) {
            return $query;
        }

        return $query->where(function ($q) use ($user) {
            // Everyone can see public documents
            $q->where('access_level', 'public')
                ->orWhere(function ($q) use ($user) {
                    $q->where('access_level', 'department')
                        ->where('department_id', $user->department_id);
                })
                ->orWhere(function ($q) use ($user) {
                    $q->where('access_level', 'private')
                        ->where('uploaded_by', $user->id);
                });
        });
    }
}
