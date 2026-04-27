<?php

namespace App\Http\Controllers\Api;

use App\Models\Book;
use App\Http\Controllers\Controller;
use App\Http\Resources\BookResource;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\File;

class BookController extends Controller
{
    /**
     * index
     *
     * @return void
     */
    public function index(Request $request)
    {
        $perPageInput = strtolower((string) $request->query('per_page', '10'));
        $allowedPerPage = ['10', '25', '50', '100', 'all'];

        if (!in_array($perPageInput, $allowedPerPage, true)) {
            $perPageInput = '10';
        }

        $perPage = $perPageInput === 'all'
            ? max(Book::count(), 1)
            : (int) $perPageInput;

        //get all books
        $books = Book::latest()->paginate($perPage);

        //return collection of books as a resource
        return new BookResource(true, 'List Data Books', $books);
    }

    public function store(Request $request)
    {
        //define validation rules
        $validator = Validator::make($request->all(), [
            'name' => 'required',
            'harga' => 'required',
            'stock' => 'required',
            'image' => 'required|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
        ]);

        //check if validation fails
        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validasi gagal.',
                'errors' => $validator->errors(),
            ], 422);
        }

        //upload image
        $image = $request->file('image');
        $imageName = $image->hashName();
        $destinationPath = public_path('storage/books');

        if (!File::exists($destinationPath)) {
            File::makeDirectory($destinationPath, 0755, true);
        }

        $image->move($destinationPath, $imageName);

        //create books
        $book = Book::create([
            'user_id' => $request->user()->id,
            'name' => $request->name,
            'harga' => $request->harga,
            'stock' => $request->stock,
            'image' => $imageName,
        ]);

        //return response
        return new BookResource(true, 'Data Book Berhasil Ditambahkan!', $book);
    }

    public function show($id)
    {
        //find book by ID
        $book = Book::find($id);

        if (!$book) {
            return response()->json([
                'message' => 'Data book tidak ditemukan.',
            ], 404);
        }

        //return single book as a resource
        return new BookResource(true, 'Detail Data Book!', $book);
    }

    public function update(Request $request, $id)
    {
        //define validation rules
        $validator = Validator::make($request->all(), [
            'name' => 'required',
            'harga' => 'required',
            'stock' => 'required',
        ]);

        //check if validation fails
        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validasi gagal.',
                'errors' => $validator->errors(),
            ], 422);
        }

        //find book by ID
        $book = Book::find($id);

        if (!$book) {
            return response()->json([
                'message' => 'Data book tidak ditemukan.',
            ], 404);
        }

        //check if image is not empty
        if ($request->hasFile('image')) {
            //upload image
            $image = $request->file('image');
            $imageName = $image->hashName();
            $destinationPath = public_path('storage/books');

            if (!File::exists($destinationPath)) {
                File::makeDirectory($destinationPath, 0755, true);
            }

            $image->move($destinationPath, $imageName);

            //delete old image
            $oldImagePath = public_path('storage/books/' . basename($book->image));
            if (File::exists($oldImagePath)) {
                File::delete($oldImagePath);
            }

            //update book with new image
            $book->update([
                'image' => $imageName,
                'name' => $request->name,
                'harga' => $request->harga,
                'stock' => $request->stock,
            ]);
        } else {
            //update post without image
            $book->update([
                'name' => $request->name,
                'harga' => $request->harga,
                'stock' => $request->stock,
            ]);
        }

        //return response
        return new BookResource(true, 'Data Book Berhasil Diubah!', $book);
    }

    public function destroy($id)
    {
        //find book by ID
        $book = Book::find($id);

        if (!$book) {
            return response()->json([
                'message' => 'Data book tidak ditemukan.',
            ], 404);
        }

        //delete image
        $imagePath = public_path('storage/books/' . basename($book->image));
        if (File::exists($imagePath)) {
            File::delete($imagePath);
        }

        //delete book
        $book->delete();

        //return response
        return new BookResource(true, 'Data Book Berhasil Dihapus!', null);
    }
}
