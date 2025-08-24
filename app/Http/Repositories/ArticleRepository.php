<?php
namespace App\Http\Repositories;

use App\Models\Article;
use App\Models\Pharmacist;
use App\Models\Reactedpeople;
use Illuminate\Support\Facades\Auth;

class ArticleRepository{


    public function addarticle($request){
        $user=Auth::id();
        $pharmacist=Pharmacist::where('user_id',$user)->first();
     if ($request->hasFile('image')) {
    $image = $request->file('image');
    $imageExtension = $image->getClientOriginalExtension();
    $imageName = time() . '_' . uniqid() . '.' . $imageExtension;
    $imagePath = 'articles';
    $image->move(public_path($imagePath), $imageName);
    $imageRelativePath = $imagePath . '/' . $imageName;
}

    $article=Article::create([
      'topic'=>$request->topic,
      'content'=>$request->content,
      'image'=>$imageRelativePath ,
      'pharmacist_id'=>$pharmacist->id,

    ]);

    return response()->json(['message' => 'Article stored successfully.'], 201);


    }


    public function showmyarticles(){
        $id=Auth::id();
         $pharmacist=Pharmacist::where('user_id',$id)->first();
         $pharmacist_id=$pharmacist->id;
         $articles=$pharmacist->articles;
         return $articles;
    }

    public function deletearticle($id){
         $user_id=Auth::id();
         $pharmacist=Pharmacist::where('user_id',$user_id)->first();
         $pharmacist_id=$pharmacist->id;
         $article=Article::where('id',$id)->where('pharmacist_id',$pharmacist_id)->delete();

    return response()->json(['message' => 'Article deleted successfully.'], 201);


    }

 public function getAllArticles()
{
    $articles = Article::with(['pharmacist.user', 'pharmacist.pharma'])->get();

    return response()->json($articles);
}

public function addlike($id){
    $user_id=Auth::id();
     $reactedpeople=Reactedpeople::where('article_id',$id)
                       ->where('user_id',$user_id)
                       ->first();
        if(!$reactedpeople)
            {
   Reactedpeople::create([
        'user_id'=>$user_id,
        'article_id'=>$id,
        'reactiontype'=>1,
    ]);
    $article=Article::where('id',$id)->first();
    $like =$article->like;
    $newlike=$like+1;
    $article->like=$newlike;
    $article->save();

    $reactedUsers = Reactedpeople::where('article_id', $id)
        ->with('user')
        ->get();

      return response()->json([
        'reactions_count' => $article->like,
        'reacted_users'   => $reactedUsers,
    ]);
        }
    else{
         $reactedUsers = Reactedpeople::where('article_id', $id)
        ->with('user')
        ->get();
             return response()->json([
        'reactions_count' => 'incorrect message',
        'reacted_users'   => $reactedUsers,
    ]);
    }

}

public function adddislike($id)
{
    $article=Article::where('id',$id)->first();
     $user_id=Auth::id();
     $reactedpeople=Reactedpeople::where('article_id',$id)
                       ->where('user_id',$user_id)
                      ->first();
     if(!$reactedpeople){
    Reactedpeople::create([
        'user_id'=>$user_id,
        'article_id'=>$id,
        'reactiontype'=>0,
    ]);
    $dislike =$article->dislike;
    $newdislike=$dislike+1;
    $article->dislike=$newdislike;
    $article->save();

        $reactedUsers = Reactedpeople::where('article_id', $id)
        ->with('user')
        ->get();

            return response()->json([
        'reactions_count' => $article->dislike,
        'reacted_users'   => $reactedUsers,
    ]);
     }

     else{
            $reactedUsers = Reactedpeople::where('article_id', $id)
        ->with('user')
        ->get();
             return response()->json([
        'reactions_count' => 'incorrect message',
        'reacted_users'   => $reactedUsers,
    ]);
     }

}

public function editcontent($request){
$article=Article::where('id',$request->id)->first();
$article->content=$request->content;
$article->save();
}

public function edittopic($request){
$article=Article::where('id',$request->id)->first();
$article->topic=$request->topic;
$article->save();
}


public function removelike($id){
    $user_id=Auth::id();
    $reactedpeople=Reactedpeople::where('article_id',$id)
                       ->where('user_id',$user_id)
                       ->where('reactiontype','1')->first();
   if($reactedpeople){
  $article=Article::where('id',$id)->first();
  $article->like=$article->like - 1;
  $article->save();
  $reactedpeople->delete();
      $reactedUsers = Reactedpeople::where('article_id', $id)
        ->with('user')
        ->get();
            return response()->json([
        'reactions_count' => $article->like,
        'reacted_users'   => $reactedUsers,
    ]);

   }
   else{

         $reactedUsers = Reactedpeople::where('article_id', $id)
        ->with('user')
        ->get();
             return response()->json([
        'reactions_count' => 'incorrect message',
        'reacted_users'   => $reactedUsers,
    ]);
}

}

public function removedislike($id){
     $user_id=Auth::id();
    $reactedpeople=Reactedpeople::where('article_id',$id)
                       ->where('user_id',$user_id)
                       ->where('reactiontype','0')->first();
if($reactedpeople){
   $article=Article::where('id',$id)->first();
   $article->dislike=$article->dislike - 1;
   $article->save();
   $reactedpeople->delete();

       $reactedUsers = Reactedpeople::where('article_id', $id)
        ->with('user')
        ->get();

            return response()->json([
        'reactions_count' => $article->dislike,
        'reacted_users'   => $reactedUsers,
    ]);
    }
else{
            $reactedUsers = Reactedpeople::where('article_id', $id)
        ->with('user')
        ->get();
             return response()->json([
        'reactions_count' => 'incorrect message',
        'reacted_users'   => $reactedUsers,
    ]);
}

}


}
