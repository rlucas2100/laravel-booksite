<?php

namespace App\Http\Controllers;

use App\Models\Book;
use App\Models\Category;
use App\Models\Comment;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class TestController extends Controller
{
    public function index()
    {
        $comment = new Comment([
           'body' => 'jkdfskjopdfsjkpfjds',
            'user_id' => 11
        ]);


        $book = Book::find(23);

        $book->comments()->save($comment);

        dd($comment, $book);

        $book = Book::find(2);
//        dd($book->comment);
        foreach ($book->comments as $comment) {
            print_r($comment);

        }

        $category = Category::find(1);
//        dd($category);
        $comment = Comment::all();
//        $comment = Comment::where();

        dd($comment);
//        dd($category->books()->update(['category_id', 34]));


//    select users that  have 0 books and show them


        // fetch books from the db & pass content to view to display them
//      using eloquent, get all the books of this logged in user and display them

        return view('test', [
            'books' => Book::showOnHomePage(4),
            'categories' => Category::all(),
            'comments' => Comment::all()
        ]);
    }

}
