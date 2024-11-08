<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable, HasRoles, SoftDeletes;

    protected $table = 'app_users';

    protected $appends = [ 'tenant_name', 'gender_text' ];
    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'tenant_id',
        'device_id',
        'ward_id',
        'department_id',
        'sub_department_id',
        'emp_code',
        'in_time',
        'name',
        'email',
        'mobile',
        'dob',
        'gender',
        'password',
        'employee_type',
        'is_app_registered',
        'is_employee',
        'shift_id',
        'designation_id',
        'clas_id',
        'doj',
        'is_ot',
        'is_divyang',
        'is_rotational',
        'permanent_address',
        'present_address',
        'created_by',
        'updated_by',
        'deleted_by',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
    ];

    public function getRoleNameAttribute()
    {
        return $this->getRoleNames();
    }
    public function getGenderTextAttribute()
    {
        return $this->gender == 'm' ? 'Male' : 'Female';
    }

    public function ward()
    {
        return $this->belongsTo(Ward::class, 'ward_id', 'id')->withTrashed();
    }

    public function tenant()
    {
        return $this->belongsTo(Tenant::class);
    }

    public function getTenantNameAttribute()
    {
        return $this->tenant->name;
    }

    public function department()
    {
        return $this->belongsTo(Department::class)->withTrashed();
    }

    public function subDepartment()
    {
        return $this->belongsTo(Department::class, 'sub_department_id', 'id');
    }

    public function employee()
    {
        return $this->hasOne(Employee::class);
    }

    public function shift()
    {
        return $this->belongsTo(Shift::class)->withTrashed();
    }

    public function empShift()
    {
        return $this->hasOne(EmployeeShift::class)->latestOfMany();
    }

    public function empShifts()
    {
        return $this->hasMany(EmployeeShift::class);
    }

    public function clas()
    {
        return $this->belongsTo(Clas::class)->withTrashed();
    }

    public function designation()
    {
        return $this->belongsTo(Designation::class)->withTrashed();
    }

    public function punches()
    {
        return $this->hasMany(Punch::class, 'emp_code', 'emp_code');
    }

    public function weekoff()
    {
        return $this->hasOne(EmployeeWeekoff::class);
    }

    public function latestWeekoff()
    {
        return $this->hasOne(EmployeeWeekoff::class)->latestOfMany();
    }

    public function leaveRequests()
    {
        return $this->hasMany(LeaveRequest::class);
    }

    public function device()
    {
        return $this->belongsTo(Device::class, 'device_id', 'DeviceId');
    }

    public function deletedBy()
    {
        return $this->belongsTo(User::class, 'deleted_by', 'id');
    }



    public static function booted()
    {
        static::created(function (self $user)
        {
            if(Auth::check())
            {
                self::where('id', $user->id)->update([
                    'created_by'=> Auth::user()->id,
                ]);
            }
        });
        static::updated(function (self $user)
        {
            if(Auth::check())
            {
                self::where('id', $user->id)->update([
                    'updated_by'=> Auth::user()->id,
                ]);
            }
        });
        static::deleting(function (self $user)
        {
            if(Auth::check())
            {
                $user->update([
                    'deleted_by'=> Auth::user()->id,
                ]);
            }
        });
    }

}
