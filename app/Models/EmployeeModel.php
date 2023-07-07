<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Ramsey\Uuid\Uuid;

class EmployeeModel extends Model
{
    use HasFactory;
    public $incrementing = false;
    protected $keyType = 'string';
    protected $table = 'employees';

    protected $fillable = [
        'id',
        'division_id',
        'position_id',
        'name',
        'code',
        'gender',
        'phone',
        'email',
        'image',
        'birthdate',
        'status',
    ];

    protected static function boot()
    {
        parent::boot();

        self::creating(function ($model) {
            $model->{$model->getKeyName()} = Uuid::uuid4()->toString();
        });
    }

    public function position()
    {
        return $this->belongsTo(PositionModel::class, 'position_id');
    }

    public function division()
    {
        return $this->belongsTo(DivisionModel::class, 'division_id');
    }
}
