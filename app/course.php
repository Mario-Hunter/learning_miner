<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Course extends Model
{
    protected $fillable = ['url','tags','name'];


    public function comments()
    {
    	return $this->hasMany(Comment::class);
    }

    public function addComent($body)
    {
    	$this->comments()->create([
                                    'body' => $body,
                                    'course_id'=>$this->id,
                                    'user_id'=>auth()->id()
                                    ]);
    }

    public function addRank($value)
    {
        
        $this->rank()->create([
                    'rank' => $value,
                    'course_id' => $this->id,
                    'user_id' => auth()->id()
            ]);
        $this->totalRankModifier($value);
    }

    public function totalRankModifier($value)
    {
        $rankCourse = $this->totalRanks;
        $rankCourse += $value;
        $this->totalRanks = $rankCourse;
        $this->save();
        $this->averageRank();
    }

    public function averageRank()
    {
        $numberOfRanks = 0;
        foreach ($this->rank()->get() as $ranking) {
            $numberOfRanks++;
        }
        
        if($numberOfRanks == 0)
            $numberOfRanks = 1;

        $rank = $this->totalRanks;
        $rank /= (float) $numberOfRanks;
        $this->rank = $rank;
        $this->save();
    }


    public function user()
    {
    	return $this->belongsTo(User::class);
    }

    public function tags(){
        return $this->belongsToMany(Tag::class);
    }

    public function rank()
    {
        return $this->hasMany(Rank::class);
    }
}
