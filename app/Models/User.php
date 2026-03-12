<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Carbon\Carbon;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    public const AURA_TIERS = [
        ['name' => 'Novice',     'min' => 0,    'max' => 499],
        ['name' => 'Contributor','min' => 500,  'max' => 1499],
        ['name' => 'Specialist', 'min' => 1500, 'max' => 2999],
        ['name' => 'Lead',       'min' => 3000, 'max' => 4999],
        ['name' => 'Architect',  'min' => 5000, 'max' => 99999],
    ];

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $guarded = ['id'];
    protected $appends = ['aura_progress', 'user_profile_view', 'is_online', 'custom_border'];

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

    public function userAura() {
        $points = $this->total_points ?? 0;

        foreach (self::AURA_TIERS as $tier) {
            if ($points >= $tier['min'] && $points <= $tier['max']) {
                $range = $tier['max'] - $tier['min'];
                if ($range <= 0) return 100;
                return round((($points - $tier['min']) / $range) * 100, 2);
            }
        }

        return 100;
    }

    public function getAuraProgressAttribute() {
        $points = $this->total_points ?? 0;
        $currentTier = null;
        $tiers = self::AURA_TIERS;

        foreach ($tiers as $tier) {
            if ($points >= $tier['min'] && $points <= $tier['max']) {
                $currentTier = $tier;
                break;
            }
        }

        if (!$currentTier) {
            $currentTier = end($tiers);
        }

        return [
            'tier' => $currentTier,
            'percentage' => $this->userAura()
        ];
    }

    public function appliedProjects() {
        return $this->hasMany(ProjectCollaborator::class, 'collaborator_id', 'id');
    }

    public function createdProjects() {
        return $this->hasMany(Projects::class, 'creator_id', 'id');
    }

    public function taskAssignments() {
        return $this->hasMany(Member::class, 'member_id', 'id');
    }

    public function tasks() {
        return $this->hasManyThrough(Task::class, Projects::class, 'creator_id', 'projects_id', 'id', 'id');
    }

    public function getIsOnlineAttribute(){
        return $this->last_seen_at && Carbon::parse($this->last_seen_at)->diffInSeconds(now()) < 20;
    }

    public function customBorder() {
        return $this->hasOne(CustomBorder::class, 'id', 'custom_border_id');
    }

    public function getCustomBorderAttribute(){
        return $this->customBorder()->first();
    }

    public function getUserProfileViewAttribute() {
        return [
            'stats' => [
                'created_projects' => $this->createdProjects,
                'deployed_tasks'=> $this->tasks()->where('tasks.creator_id', $this->id)->count(),
                'accomplished_tasks' => $this->tasks()
                    ->where('tasks.creator_id', $this->id)
                    ->where('tasks.status', "COMPLETED")
                    ->count(),

                // member
                'assigned_tasks'   => $this->taskAssignments()->count(),
                'finished_tasks'   => Task::whereHas('members', function($q) {
                    $q->where('member_id', $this->id);
                })->where('status', 'COMPLETED')->count(),
            ],
            'rank_meta' => $this->aura_progress
        ];
    }
}
