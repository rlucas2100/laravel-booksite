<?php

namespace App\Http\Controllers;

use App\Models\Book;
use App\Models\Category;
use App\Models\Podcast;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class CategoryController extends Controller
{

    public function __construct()
    {
        $this->authorizeResource(Category::class, 'category');
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\Factory|\Illuminate\Contracts\View\View
     */


    public function index()
    {
        return view('categories.index', [
            'categories' => Category::all()
        ]);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\Factory|\Illuminate\Contracts\View\View
     */
    public function create()
    {
        return view('categories.create');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Http\RedirectResponse|\Illuminate\Routing\Redirector
     */
    public function store(Request $request)
    {
        $attributes = request()->validate([
            'name' => 'required'
        ]);

        $attributes['slug'] = Str::slug($request->name);

        Category::create($attributes);

        return redirect('/categories');
    }

    /**
     * Display the specified resource.
     *
     * @param int $id
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\Factory|\Illuminate\Contracts\View\View
     */


    public function show(Category $category): \Illuminate\Contracts\View\View|\Illuminate\Contracts\View\Factory|\Illuminate\Contracts\Foundation\Application
    {

//        return view('categories.show')->with('category', $category);

//        return view('admin.books.index', [
//            'books' => Book::paginate(50)
//        ]);

//
//        'books' is variable i am passing into show view
//        $category is the cat you click on home page, categories/aut in url
        return view('categories.show', [
            'books' => Book::where('category_id', $category->id)->paginate(3),
            'podcasts' => Podcast::all()
            ])->with('category', $category);
    }

    public function allCatBooks(Category $category): \Illuminate\Contracts\View\Factory|\Illuminate\Contracts\View\View|\Illuminate\Contracts\Foundation\Application
    {

        return view('categories.all-cat-books')->with('category', $category)->paginate(5);
    }

//    public function getCatBooks()
//    {
//        return Book::paginate(3);
//    }
    /**
     * Show the form for editing the specified resource.
     *
     * @param int $id
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\Factory|\Illuminate\Contracts\View\View
     */

    public function edit(Category $category): \Illuminate\Contracts\View\Factory|\Illuminate\Contracts\View\View|\Illuminate\Contracts\Foundation\Application
    {
        return view('categories.edit')->with('category', $category);

    }

    /**
     * Update the specified resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @param int $id
     * @return \Illuminate\Http\RedirectResponse
     */

    public function update(Request $request, Category $category): \Illuminate\Http\RedirectResponse
    {

        $attributes = request()->validate([
            'name' => 'required'
        ]);

        $attributes['slug'] = Str::slug($request->name);

        $category->update($attributes);
        return to_route('categories.index', $category)->with('success', 'Category updated');

    }

    /**
     * Remove the specified resource from storage.
     *
     * @param int $id
     * @return \Illuminate\Http\RedirectResponse
     */

    public function destroy(Category $category, Request $request): \Illuminate\Http\RedirectResponse
    {
        // 1. fetch existing books from the target category
        // 2. change the category for all fetched books to alternative category
        $category->books()->update(['category_id' => $request->alt_category_id]);

        // 3. delete the target category
        $category->delete();
        return redirect('categories');
    }
}

// comments table
// a book can have many comments. one to many

// comments on books one relationship in mind
