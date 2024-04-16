<?php

namespace App\Http\Controllers;

use App\Jobs\MakeComment;
use App\Models\Comment;
use App\Rules\FileUploadRule;
use App\Rules\ParentIdRule;
use App\Rules\TextRule;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;

class CommentController extends Controller
{
    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'user_name' => [
                'required',
                'regex:/^[a-zA-Z0-9]+$/'
            ],
            'email' => [
                'required',
                'email'
            ],
            'text' => [
                'required',
                new TextRule()
            ],
            'parent_id' => [
                'sometimes',
                new ParentIdRule()
            ],
            'file' => [
                'sometimes',
                'file',
                new FileUploadRule()
            ]
            // Добавьте здесь валидацию для CAPTCHA
        ]);

        // Создаем новый экземпляр комментария
        $comment = new Comment();
        $comment->fill($validatedData);

        // Если файл прикреплен, сохраняем его в папку storage/app/public/uploads
        if ($request->hasFile('file')) {
            $file = $request->file('file');
            $fileName = $file->getClientOriginalName();
            $fileName = $this->generateFileName($fileName);
            $type = explode('.', $fileName);
            $type = end($type);
            $file_type = 1; // image
            if($type == "txt") {
                $file_type = 2; // txt
            }
            // Сохраняем файл в папку storage/app/public/uploads
            Storage::putFileAs('public/uploads', $file, $fileName);
            // Записываем путь к файлу в колонку file
            $comment->file = "/storage/uploads/$fileName";
            $comment->file_type = $file_type;
        }
        // Сохраняем комментарий
        $comment->save();

        dispatch(new MakeComment($comment));


        return response()->json(['success' => true, 'message' => 'Comment added successfully', 'data' => $comment]);
    }

    private function generateFileName($file) {
        if(file_exists("/storage/uploads/$file")) {
            $file = md5( rand() . $file);
            return $this->generateFileName($file);
        }
        return $file;
    }


    public function index()
    {
        return view('welcome');
    }

    public function getComments(Request $request)
    {
        $sortBy = $request->get('sortBy') ?? 'created_at';
        $sortOrder = $request->get('sortOrder') ?? 'desc';
        $page = $request->get('page') ?? 1;
        return Cache::remember("comment-$sortBy-$sortOrder-$page", 600 ,function () use ($sortBy, $sortOrder, $page) {
            return Comment::with('children')
                ->whereNull('parent_id')
                ->orderBy($sortBy, $sortOrder)
                ->paginate(25);
        });
    }


}
