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
        $this->dynamicRank();
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

    public function dynamicRank()
    {
        $averageRank = $this->rank;
        $user = $this->user()->get();
        $sd = $this->standardDeviation();
        $differenceFactor = $user[0]->user_score - $averageRank;
        
        if($differenceFactor < 0)
            $differenceFactor = 0;

        $searchRank = $averageRank * (1 + ($differenceFactor/($averageRank + 3 * $sd)));

        $this->searchRank = $searchRank;
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

    public function buttonCases()
    {
        $idPrev = $this->id;
        $idPrev--;
        $idNext = $this->id;
        $idNext++;

        $coursePrev = Course::where('id',"$idPrev")->get();
        $courseNext = Course::where('id',"$idNext")->get();

        $prevFlag = false;
        $nextFlag = false;

        foreach ($coursePrev as $key) {
            $prevFlag = true;
        }

        foreach ($courseNext as $key) {
            $nextFlag = true;
        }


        if($nextFlag && $prevFlag)
            return 1;
        else if(!$nextFlag && $prevFlag)
            return 2;
        else if($nextFlag && !$prevFlag)
            return 3;
    }

    public function insertTags($course,$tags)
    {
        foreach($tags as $newTag)
        {
          $bannedWords= "is in at or on ";

          if (stripos($bannedWords, $newTag) == 0 && !is_numeric($newTag) )
          {
            $tag = Tag::firstOrCreate(['name'=>$newTag]);
            
            $course->tags()->syncWithoutDetaching([$tag->id]);
          }
        }
    }

    public function standardDeviation()
    {
        $ranks = $this->rank()->get();

        $r1 = 0;
        $r2 = 0;
        $r3 = 0;
        $r4 = 0;
        $r5 = 0; 

        $r = 0;
        
        foreach ($ranks as $rank) {
             
            if($rank->rank == 1)
                $r1++;
            else if($rank->rank == 2)
                $r2++;
            else if($rank->rank == 3)
                $r3++;
            else if($rank->rank == 4)
                $r4++;
            else if($rank->rank == 5)
                $r5++;

            $r++;
         } 

         $mean = (float)(5*$r5 + 4*$r4 + 3*$r3 + 2*$r2 + 1*$r1)/$r;


         $variance = (float)(25*$r5 + 16*$r4 + 9*$r3 + 4*$r2 + 1*$r1)/$r;

         $standardDeviation = sqrt((float)($variance - pow($mean, 2)));

         return $standardDeviation;
    }

}
