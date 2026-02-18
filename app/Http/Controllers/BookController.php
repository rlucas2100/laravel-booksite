<?php

namespace App\Http\Controllers;

use App\Models\Book;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class BookController extends Controller
{

    /**
     * Create the controller instance.
     *
     * @return void
     */

    public function __construct()
    {
        $this->authorizeResource(Book::class, 'book');
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\Factory|\Illuminate\Contracts\View\View
     */


    public function index()
    {

        if (isAdmin())
            return $this->adminDashboard();

        return $this->welcome();
    }

    public function adminDashboard(): \Illuminate\Contracts\View\Factory|\Illuminate\Contracts\View\View|\Illuminate\Contracts\Foundation\Application
    {
        return view('admin.books.index', [
            'books' => Book::paginate(50)
        ]);
    }

    public function welcome(): \Illuminate\Contracts\View\Factory|\Illuminate\Contracts\View\View|\Illuminate\Contracts\Foundation\Application
    {
        return view('welcome', [
            'books' => Book::showOnHomePage(),
            'categories' => Category::all()
        ]);
    }
// a Comment model might belong to both the Book and Podcast models.
//    public function categoryBooks(): \Illuminate\Contracts\View\Factory|\Illuminate\Contracts\View\View|\Illuminate\Contracts\Foundation\Application
//    {
//        return view ('category.show', [
//            'books' => Book::paginate(50)
//        ]);
//    }

    public function myBooks(): \Illuminate\Contracts\View\Factory|\Illuminate\Contracts\View\View|\Illuminate\Contracts\Foundation\Application
    {

        // fetch books from the db & pass content to view to display them
        $books = \auth()->user()->books;
        return view('books.index')->with('books', $books);

    }


    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\Factory|\Illuminate\Contracts\View\View
     */
    public function create(): \Illuminate\Contracts\View\View|\Illuminate\Contracts\View\Factory|\Illuminate\Contracts\Foundation\Application
    {
        return view('books.create');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Http\RedirectResponse|\Illuminate\Routing\Redirector
     */
    public function store(Request $request): \Illuminate\Routing\Redirector|\Illuminate\Http\RedirectResponse|\Illuminate\Contracts\Foundation\Application
    {

        $attributes = request()->validate([
            'title' => 'required',
            'thumbnail' => 'required|image',
            'description' => 'required',
            'pdf' => 'required|mimes:pdf',
            'category_id' => ['required', Rule::exists('categories', 'id')]
        ]);

        $attributes['slug'] = Str::slug($request->title);
        $attributes['uuid'] = Str::uuid();
        $attributes['user_id'] = auth()->id();
        $attributes['thumbnail'] = \request()->file('thumbnail')->store('thumbnails');
        $attributes['pdf'] = \request()->file('pdf')->store('pdfs');

        Book::create($attributes);


        return redirect('/');
    }


    /**
     * Display the specified resource.
     *
     * @param int $id
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\Factory|\Illuminate\Contracts\View\View
     */

//    replace the primary key with the uuid of each book
//route model binding. changed $id to $uuid to Book $book
    public function show(Book $book): \Illuminate\Contracts\View\Factory|\Illuminate\Contracts\View\View|\Illuminate\Contracts\Foundation\Application
    {

        return view('books.show')->with('book', $book);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param int $id
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\Factory|\Illuminate\Contracts\View\View
     */
//    $id param changed to Book $book
    public function edit(Request $request, Book $book): \Illuminate\Contracts\View\View|\Illuminate\Contracts\View\Factory|\Illuminate\Contracts\Foundation\Application
    {

        return view('books.edit')->with('book', $book);

    }

    /**
     * Update the specified resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @param int $id
     * @return \Illuminate\Http\RedirectResponse
     */

//    $id param changed to Book $book
    public function update(Request $request, Book $book): \Illuminate\Http\RedirectResponse
    {

//    if it's the authorized user, all of this is done

        $attributes = request()->validate([
            'title' => 'required',
            'thumbnail' => 'required|image',
            'description' => 'required',
            'pdf' => 'required|mimes:pdf',
            'category_id' => ['required', Rule::exists('categories', 'id')]
        ]);

        $attributes['slug'] = Str::slug($request->title);
        $attributes['uuid'] = Str::uuid();
//        $attributes['user_id'] = auth()->id();
        $attributes['thumbnail'] = \request()->file('thumbnail')->store('thumbnails');
        $attributes['pdf'] = \request()->file('pdf')->store('pdfs');

        $book->update($attributes);
        return to_route('books.show', $book)->with('success', 'Book updated');

    }

    /**
     * Remove the specified resource from storage.
     *
     * @param int $id
     * @return \Illuminate\Http\RedirectResponse
     */


    public function destroy(Request $request, Book $book)
    {

        $book->delete();
        return to_route('my-books')->with('success', 'Book deleted');
    }
}
