<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Requests\Admin\StoreBlogRequest;
use App\Http\Requests\Admin\UpdateBlogRequest;
use App\Models\Blog;
use App\Models\Category;
use App\Models\Cat;
use Illuminate\Support\Facades\Storage;
// use Illuminate\Support\Facades\Auth;

class AdminBlogController extends Controller
{
    // ブログ一覧画面
    public function index()
    {
        $blogs = Blog::latest('updated_at')->paginate(10);
        // $user = \Illuminate\Support\Facades\Auth::user();
        return view('admin.blogs.index', ['blogs' => $blogs]);
    }

    // ブログ投稿画面
    public function create()
    {
        return view('admin.blogs.create');
    }

    // ブログ投稿処理
    public function store(StoreBlogRequest $request)
    {
        $savedImagePath = $request->file('image')->store('blogs', 'public');
        $blog = new Blog($request->validated());
        $blog->image = $savedImagePath;
        $blog->save();

        return to_route('admin.blogs.index')->with('success', 'ブログを投稿しました');
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    // 指定したIDの画面編集
    public function edit(Blog $blog)
    {
        $categories = Category::all();
        // $user = Auth::user();
        $cats = Cat::all();
        // $blog = Blog::findOrFail($id);
        return view('admin.blogs.edit', ['blog' => $blog, 'categories' => $categories, 'cats' => $cats]);
    }

    // 更新処理
    public function update(UpdateBlogRequest $request, string $id)
    {
        $blog = Blog::findOrFail($id);
        $updateData = $request->validated();

        // 画像を変更する場合
        if ($request->has(key:'image')) {
            // 変更前の画像削除
            Storage::disk(name:'public')->delete($blog->image);
            // 変更後の画像アップロード、保存パスを更新データにセット
            $updateData['image'] = $request->file(key:'image')->store(path:'blogs', options:'public');
        }
        $blog->category()->associate($updateData['category_id']);
        $blog->cats()->sync($updateData['cats'] ?? []);
        $blog->update($updateData);

        return to_route('admin.blogs.index')->with('success', 'ブログを更新しました');

    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $blog = Blog::findOrFail($id);
        $blog->delete();
        Storage::disk(name:'public')->delete($blog->image);

        return to_route('admin.blogs.index')->with('success', 'ブログを削除しました');

    }
}
