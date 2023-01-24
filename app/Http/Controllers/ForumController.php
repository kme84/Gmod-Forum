<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Auth;

use App\Models\User;
use App\Models\Chapters;
use App\Models\Topics;
use App\Models\Posts;
use App\Models\Comment;


class ForumController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }
    
    public function forum()
    {
        return view('forum', ['chapters' => Chapters::all(), 'topics' => Topics::all()]);
    }

    public function forum_addchapter(Request $request)
    {
        $chapter = new Chapters();

        $this->authorize('add', $chapter);

        $valid = $request->validate([
            'name' => 'required|min:3|max:25',
        ]);

        
        $chapter->name = $request->input('name');

        $chapter->save();

        return redirect('forum');
    }

    public function forum_deletechapter(Request $request)
    {

        $valid = $request->validate([
            'id' => 'required',
        ]);

        $chapter = Chapters::findOrFail($request->input('id'));
        $this->authorize('delete', $chapter);

        Topics::where('chapter', $request->input('id'))->delete();

        $chapter->delete();

        return redirect('forum');
    }

    public function forum_addtopic(Request $request)
    {
        
        $valid = $request->validate([
            'id' => 'required',
            'name' => 'required|min:3|max:25',
        ]);

        $topic = new Topics();
        $this->authorize('add', [$topic, $request->input('id')]);

        $topic->chapter = $request->input('id');
        $topic->name = $request->input('name');

        $topic->save();

        return redirect('forum');
    }

    public function forum_deletetopic(Request $request)
    {
        $valid = $request->validate([
            'id' => 'required',
        ]);

        $topic = Topics::findOrFail($request->input('id'));
        $this->authorize('delete', [$topic, $topic->chapter]);

        $topic->delete();

        return redirect('forum');
    }

    public function forum_topic($id)
    {
        $topic = Topics::findOrFail($id);
        $posts = Posts::where('topic', $id)->get();
        return view('forum/topic', ['topic' => $topic, 'posts' => $posts]);
    }

    public function forum_post($id)
    {
        $post = Posts::findOrFail($id);
        $topic = Topics::findOrFail($post->topic);
        $author = User::findOrFail($post->author);
        $comments = Comment::where('post', $id)->get();
        $users = array();
        foreach ($comments as $key => $value) {
            $users[$key] = User::findOrFail($value->author);
        }
        return view('forum/post', ['id' => $id, 'topic' => $topic, 'post' => $post, 'author' => $author, 'comments' => $comments, 'users' => $users]);
    }

    public function forum_addpost(Request $request)
    {
        //$data = str_replace( '&', '&amp;', $data );
        $post = new Posts();
        $this->authorize('add', $post);

        $valid = $request->validate([
            'id' => 'required',
            'name' => 'required|min:3|max:25',
            'editor' => 'required|min:10|max:1000',
        ]);
        
        $content = $request->input('editor');
        $find_pattern = '<img src=\"https?\:\/\/g-vector\.ru\/storage\/temp\/([a-zA-Z0-9]+\.[a-zA-Z0-9]+)\">';
        preg_match_all($find_pattern, $content, $matches);

        foreach ($matches[1] as $value) {
            Storage::disk('public')->move('temp/' . $value, 'uploads/' . $value);
        }
        $replace_patternt = '/<img src=\"https?\:\/\/g-vector\.ru\/storage\/temp\//';
        $content = preg_replace($replace_patternt, '<img class="img-fluid" src="http://g-vector.ru/storage/uploads/', $content);

        
        $post->topic = $request->input('id');
        $post->title = $request->input('name');
        $post->content = $content;
        $post->author = Auth::id();

        $post->save();

        return redirect('forum/'.$post->topic);
    }

    public function forum_deletepost(Request $request)
    {
        $valid = $request->validate([
            'id' => 'required',
        ]);

        $post = Posts::findOrFail($request->id);
        $this->authorize('delete', $post);

        $find_pattern = '<img class="img-fluid" src=\"https?\:\/\/g-vector\.ru\/storage\/(uploads\/[a-zA-Z0-9]+\.[a-zA-Z0-9]+)\">';
        preg_match_all($find_pattern, $post->content, $matches);

        foreach ($matches[1] as $value) {
            Storage::disk('public')->delete($value);
        }
        $topic = $post->topic;
        $post->delete();

        return redirect('forum/'.$topic);
    }

    public function forum_addcomment(Request $request)
    {
        //$data = str_replace( '&', '&amp;', $data );
        $comment = new Comment();
        $this->authorize('add', $comment);

        $valid = $request->validate([
            'id' => 'required',
            'editor' => 'required|min:10|max:1000',
        ]);
        
        $content = $request->input('editor');
        $find_pattern = '<img src=\"https?\:\/\/g-vector\.ru\/storage\/temp\/([a-zA-Z0-9]+\.[a-zA-Z0-9]+)\">';
        preg_match_all($find_pattern, $content, $matches);

        foreach ($matches[1] as $value) {
            Storage::disk('public')->move('temp/' . $value, 'uploads/' . $value);
        }
        $replace_patternt = '/<img src=\"https?\:\/\/g-vector\.ru\/storage\/temp\//';
        $content = preg_replace($replace_patternt, '<img class="img-fluid" src="http://g-vector.ru/storage/uploads/', $content);

        
        $comment->post = $request->input('id');
        $comment->content = $content;
        $comment->author = Auth::id();

        $comment->save();

        return redirect('forum/post/'.$comment->post);
    }

    public function forum_deletecomment(Request $request)
    {
        $comment = new Comment();
        $this->authorize('delete', $comment);

        $valid = $request->validate([
            'id' => 'required',
        ]);

        $comment = Comment::findOrFail($request->input('id'));

        $post_id = $comment->post;

        $comment->delete();

        return redirect('forum/post/'.$post_id);
    }

    public function editor_image_upload(Request $request)
    {
        $file = $request->file('upload');
        $path = $file->store('temp', 'public');
        return json_encode(['url' => asset('storage/'.$path)]);
    }
}