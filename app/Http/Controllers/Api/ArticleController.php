<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Article;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Validator;

class ArticleController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $article = Article::latest()->get();
        if ($article) {
            return response()->json([
                'status' => Response::HTTP_OK,
                'message' => 'List Article',
                'data' => $article
            ]);
        } else {
            return response()->json([
                'status' => Response::HTTP_NOT_FOUND,
                'message' => 'List Article',
                'error' => $article
            ]);
        }
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'title' => 'required',
            'category_name' => 'required',
            'content' => 'required'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'errors' => $validator->messages()
            ]);
        }

        if ($request->hasFile('image')) {
            $file = $request->file('image');
            $uploadFile = time() . '_' . $file->getClientOriginalName();
            $file->move('uploads/', $uploadFile);
        }

        $article = Article::create([
            'title' => $request->title,
            'category_name' => $request->category_name,
            'content' => $request->content,
            'image' => $uploadFile
        ]);

        return response()->json([
            'status' => Response::HTTP_OK,
            'message' => 'Stored Success',
            'data' => $article
        ]);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $article = Article::findOrFail($id);
        if (!$article) {
            return response()->json('Data Not Found', Response::HTTP_NOT_FOUND);
        }

        return response()->json([
            'status' => Response::HTTP_OK,
            'message' => 'List Data',
            'article' => $article
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'title' => 'required',
            'category_name' => 'required',
            'content' => 'required'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'errors' => $validator->messages()
            ]);
        }

        $article = Article::findOrFail($id);

        if ($request->hasFile('image')) {
            if (File::exists('uploads/' . $article->image)) {
                File::delete('uploads/' . $article->image);
            }

            $file = $request->file('image');
            $uploadFile = time() . '_' . $file->getClientOriginalName();
            $file->move('uploads/', $uploadFile);
            $article->image = $uploadFile;
        }

        $article->update([
            'title' => $request->title,
            'category_name' => $request->category_name,
            'content' => $request->content,

        ]);
        return response()->json([
            'status' => Response::HTTP_OK,
            'message' => 'Update Successfull',
            'article' => $article
        ]);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $article = Article::findOrFail($id);
        if (File::exists('uploads/' . $article->image)) {
            File::delete('uploads/' . $article->image);
        }
        $article->delete();
        return response()->json([
            'message' => 'Delete Successfull',
            'status' => Response::HTTP_OK,
        ]);
    }
}
