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
}
