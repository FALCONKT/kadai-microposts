<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Micropost extends Model
{
    protected $fillable = ['content'];

    /**
     * この投稿を所有するユーザ。（ Userモデルとの関係を定義）
     */
    //一 対 多　の関係強化
    public function user()
    {
        return $this->belongsTo(User::class);
    }
    

    // 多　対　多 の関係強化   
    // userの情報をつなぐ
    // User　がお気に入り されている　つぶやき
    public function favorite_users(){
        //UserClassの中の　中間Table名　の中の　micropost_id　と　user_id　を返す
        // 更に　その時間を保存
        return $this->belongsToMany(User::class,'favorites','micropost_id','user_id')->withTimestamps();
    }

    
}
