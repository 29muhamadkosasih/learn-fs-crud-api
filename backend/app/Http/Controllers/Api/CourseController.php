<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\CourseResource;
use App\Models\Course;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class CourseController extends Controller
{
    /**
     * index
     *
     * @return void
     */
    public function index()
    {
        //get all Courses
        $courses = Course::latest()->paginate(5);

        //return collection of Courses as a resource
        return new CourseResource(true, 'List Data Courses', $courses);
    }

    public function store(Request $request)
    {
        //define validation rules
        $validator = Validator::make($request->all(), [
            'title' => 'required',
            'instructor' => 'required',
            'duration' => 'required',
            'price' => 'required|numeric|min:0',
            'description' => 'nullable',
        ]);

        //check if validation fails
        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $course = Course::create([
            'user_id' => $request->user()->id,
            'title' => $request->title,
            'instructor' => $request->instructor,
            'duration' => $request->duration,
            'price' => $request->price,
            'description' => $request->description,
        ]);

        //return response
        return new CourseResource(true, 'Data Course Berhasil Ditambahkan!', $course);
    }

    public function show($id)
    {
        //find product by ID
        $course = Course::find($id);

        //return single product as a resource
        return new CourseResource(true, 'Detail Data Course!', $course);
    }

    public function update(Request $request, $id)
    {
        //define validation rules
        $validator = Validator::make($request->all(), [
            'title' => 'required',
            'instructor' => 'required',
            'duration' => 'required',
            'price' => 'required|numeric|min:0',
            'description' => 'nullable',
        ]);

        //check if validation fails
        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        //find product by ID
        $course = Course::find($id);

        $course->update([
            'title' => $request->title,
            'instructor' => $request->instructor,
            'duration' => $request->duration,
            'price' => $request->price,
            'description' => $request->description,
        ]);

        //return response
        return new CourseResource(true, 'Data Course Berhasil Diubah!', $course);
    }

    public function destroy($id)
    {
        //find product by ID
        $course = Course::find($id);
        $course->delete();

        //return response
        return new CourseResource(true, 'Data Course Berhasil Dihapus!', null);
    }
}
