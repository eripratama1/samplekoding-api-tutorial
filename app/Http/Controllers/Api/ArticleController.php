<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Article;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Validator;

use function PHPUnit\Framework\isEmpty;

class ArticleController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        /**
         * Menampilkan listing dari tabel artikel
         * Jika tabel masih kosong akan me-return response JSON dengan
         * status HTTP_NO_CONTENT 204
         * 
         * Jika tidak maka akan menampilkan list artikel
         */
        $article = Article::latest()->get();
        if ($article->isEmpty()) {
            return response()->json([
                'status' => Response::HTTP_NO_CONTENT,
                'message' => 'Belum ada postingan artikel',
            ]);
        } else {
            return response()->json([
                'status' => Response::HTTP_OK,
                'message' => 'List Article',
                'article' => $article
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
        /**
         * Proses Validasi data menggunakan facade validator
         */
        $validator = Validator::make($request->all(), [
            'title' => 'required',
            'category_name' => 'required',
            'content' => 'required'
        ]);

        /**
         * Jika pada saat proses Validasi data ditemukan error
         * maka akan menampilkan pesan error validasi
         */
        if ($validator->fails()) {
            return response()->json([
                'errors' => $validator->messages()
            ]);
        }

        /**
         * Proses input data ke table artikel
         */
        $article = new Article;

        /**
         * Jika memiliki inputan gambar kode dibawah akan di jalankan
         * lalu jalankan proses simpan data.
         * 
         * Jika tidak memiliki inputan gambar langsung menjalankan 
         * proses simpan data.
         */
        if ($request->hasFile('image')) {
            $file = $request->file('image');
            $uploadFile = time() . '_' . $file->getClientOriginalName();
            $file->move('uploads/', $uploadFile);
            $article->image = $uploadFile;
        }

        $article->title = $request->title;
        $article->category_name = $request->category_name;
        $article->content = $request->content;
        $article->save();

        /**
         * Return response yang di dapat jika proses
         * simpan data berhasil
         */
        return response()->json([
            'status' => Response::HTTP_OK,
            'message' => 'Stored Success',
            'article' => $article
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
        /**
         * Menampilkan single data dari tabel artikel
         * Jika data ada tampilkan datanya
         * 
         * jika tidak tampilkan response NOT_FOUND
         */
        $article = Article::find($id);
       
        if ($article) {
            return response()->json([
                'status' => Response::HTTP_OK,
                'message' => 'Data-' . $article->id,
                'article' => $article
            ]);
        } else {
            return response()->json([
                'status' => Response::HTTP_NOT_FOUND,
                'message' => 'Data Not Found'
            ]);
        }
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

        /**
         * Proses Validasi data menggunakan facade validator
         */
        $validator = Validator::make($request->all(), [
            'title' => 'required',
            'category_name' => 'required',
            'content' => 'required'
        ]);

        /**
         * Jika pada saat proses Validasi data ditemukan error
         * maka akan menampilkan pesan error validasi
         */
        if ($validator->fails()) {
            return response()->json([
                'errors' => $validator->messages()
            ]);
        }

        $article = Article::findOrFail($id);

        /**
         * Jika memiliki inputan gambar lakukan proses hapus data
         * gambar yang lama lalu simpan gambar yang baru
         * 
         * Jika tidak memiliki inputan gambar langsung menjalankan 
         * proses update data.
         */
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

         /**
         * Return response yang di dapat jika proses
         * update data berhasil
         */
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
        /**
         * Cari data yang akan dihapus dan jika data tersebut
         * memiliki file gambar hapus juga gambar tersebut lalu
         * tampilkan responsenya.
         */
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
