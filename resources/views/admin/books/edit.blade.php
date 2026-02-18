<x-app-layout>

    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Edit book') }}
        </h2>
    </x-slot>
    <section>
        <form method="POST" action="{{route('books.update', $book )}}" enctype="multipart/form-data">
            @method('put')
            @csrf


            <div class="mb-6">
                <label class="block mb-2 uppercase font-bold text-xs"
                       for="Title"
                >
                    Title

                </label>

                <input class="border border-gray-400 p-2 w-full"
                       type="text"
                       name="title"
                       id="title"
                       value="{{ old('title', $book->title) }}"
                       required
                >

                @error('title')
                <p class="text-red-500">{{ $message }}</p>
                @enderror

            </div>

            <div class="mb-6">
                <label class="block mb-2 uppercase font-bold text-xs"
                       for="Slug"
                >
                    Slug

                </label>

                <input class="border border-gray-400 p-2 w-full"
                       type="text"
                       name="slug"
                       id="slug"
                       value="{{ old('slug', $book->slug) }}"

                       required
                >

                @error('slug')
                <p class="text-red-500">{{ $message }}</p>
                @enderror

            </div>

            <div class="mb-6">
                <label class="block mb-2 uppercase font-bold text-xs"
                       for="Thumbnail"
                >
                    Thumbnail

                </label>

                <input class="border border-gray-400 p-2 w-full"
                       type="file"
                       name="thumbnail"
                       id="thumbnail"
                       value="{{old('thumbnail', $book->thumbnail)}}"
                >

                @error('thumbnail')
                <p class="text-red-500">{{ $message }}</p>
                @enderror

            </div>

            <div class="mb-6">
                <label class="block mb-2 uppercase font-bold text-xs"
                       for="Description"
                >
                    Description

                </label>

                <input class="border border-gray-400 p-2 w-full"
                       type="text"
                       name="description"
                       id="description"
                       value="{{ old('description', $book->description) }}"


                >

                @error('description')
                <p class="text-red-500">{{ $message }}</p>
                @enderror

            </div>

            <div class="mb-6">
                <label class="block mb-2 uppercase font-bold text-xs"
                       for="Book"
                >
                    Book pdf

                </label>

                <input class="border border-gray-400 p-2 w-full"
                       type="file"
                       name="pdf"
                       id="pdf"
                       value="{{old('pdf', $book->pdf)}}"

                >

                @error('pdf')
                <p class="text-red-500">{{ $message }}</p>
                @enderror

            </div>

            <div class="mb-6">
                <label class="block mb-2 uppercase font-bold text-xs"
                       for="Category"
                >
                    Category
                </label>

                <select name="category_id" id="category_id">
                    @php
                        $categories = \App\Models\Category::all();
                    @endphp

                    @foreach ($categories as $category)
                        <option
                            value="{{ $category->id }}" {{ old('category_id', $book->category_id) == $category->id ? 'selected' : '' }}
                        >{{ $category->name }} </option>
                    @endforeach
                </select>

                @error('category_id')
                <p class="text-red-500">{{ $message }}</p>
                @enderror

                <div class="mb-6">

                    <button type="submit" class="">
                        Submit
                    </button>
                </div>

            </div>
        </form>

    </section>
</x-app-layout>
