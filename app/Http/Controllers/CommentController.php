<?php

namespace App\Http\Controllers;

use App\Models\Book;
use App\Models\Comment;
use Illuminate\Http\Request;

class CommentController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
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
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\RedirectResponse
     */

    public function store(Comment $comment, Request $request)
    {


//        dd($input);

        // validation
//        request()->validate([
//            'body' => 'required',
//
//        ]);

        $attributes = request()->validate([
            'body' => 'required',
        ]);
        $attributes['user_id'] = request()->user()->id;
        $attributes['book_id'] = Book::find($request['book_id']);
        $request = $request->all();

//        $book = Book::find($request['book_id']);
//        $book->comments()->save($comment);
        $attributes->comments()->save($attributes);


//        $comment->comments()->save($attributes);

//        Comment::create($attributes);
//        $request = $request->all();
//        $book = Book::find($request['book_id']);
//        $book->comments()->save($comment);


        // add a comment to the given post
//        $comment->commentable()->create([
//            'user_id' => request()->user()->id,
//            'body' => request('body')
//        ]);

        return back();
    }

    /**
     * Display the specified resource.
     *
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }
}
