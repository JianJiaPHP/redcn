<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Tymon\JWTAuth\Contracts\JWTSubject;

class Users extends Authenticatable implements JWTSubject
{
    use Notifiable;

    protected $connection = 'mysql';
    protected $table = 'users';
    protected $hidden = ['password'];
    protected $guarded = [];

    // Rest omitted for brevity

    /**
     * Get the identifier that will be stored in the subject claim of the JWT.
     *
     * @return mixed
     */
    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    /**
     * Return a key value array, containing any custom claims to be added to the JWT.
     *
     * @return array
     */
    public function getJWTCustomClaims()
    {
        return [];
    }

    # 创建时间
    public function getCreatedAtAttribute($value)
    {
        return Carbon::parse($value)->toDateTimeString();
    }
    # 更新时间
    public function getUpdatedAtAttribute($value)
    {
        return Carbon::parse($value)->toDateTimeString();
    }

    # 获取我的下级 以及我的下级的下级的userId
    public static function getMySubordinateUserId($userId,$p = null): array
    {
        if ($p == 1){
            return self::query()->where('p_id', $userId)->pluck('id')->toArray();
        }elseif($p == 2){
            return self::query()->whereIn('p_id', self::query()->where('p_id', $userId)->pluck('id')->toArray())->pluck('id')->toArray();
        }else{
            $userIds = self::query()->where('p_id', $userId)->pluck('id')->toArray();
            return array_merge($userIds, self::query()->whereIn('p_id', $userIds)->pluck('id')->toArray());
        }
    }

    # 团队总业绩
    public static function getMySubordinateTotalIncome($userId): float
    {
        $userIds = self::getMySubordinateUserId($userId);
        $userIds = array_merge($userIds,[$userId]);
        $sum = UserAccount::query()->where('type',5)->whereIn('user_id', $userIds)->sum('profit');
        return abs($sum);
    }

    # 获取用户的业绩
    public static function getUserIdIncome($userId){
        return abs(UserAccount::query()->where('type',5)->where('user_id',$userId)->sum('profit'));
    }

    # 头像切割
    public function getAvatarAttribute($value)
    {
        return env('APP_URL') .'/'. $value;
    }
}
