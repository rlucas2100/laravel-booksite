<x-app-layout>

    <x-slot name="header">
        {{--        <h2 class="font-semibold text-xl text-gray-800 leading-tight">--}}
        {{--            {{ __('New book') }}--}}
        {{--        </h2>--}}
    </x-slot>
    <h2 class="mt-1 font-semibold text-xl leading-tight inline-flex mx-2 text-black">
        {{ __('Upload Book') }}
    </h2>
    <section class="mx-7">
        <form class="rounded px-8 pt-6 pb-8 mb-4" method="POST" action="{{route('books.store')}}"
              enctype="multipart/form-data">
            @csrf

            <div>
                <x-input-label for="title" :value="__('Title')"/>

                <x-text-input id="title" class="block mt-1 w-7/12" type="text" name="title" :value="old('name')"
                              required autofocus/>

                <x-input-error :messages="$errors->get('title')" class="mt-2"/>
            </div>


            <div class="mt-4">
                <x-input-label for="thumbnail" :value="__('Thumbnail')"/>

                <x-text-input id="thumbnail" class="block mt-1 w-7/12" type="file" name="thumbnail"
                              :value="old('thumbnail')" required autofocus/>

                <x-input-error :messages="$errors->get('thumbnail')" class="mt-2"/>
            </div>


            <div class="mt-4">
                <x-input-label for="description" :value="__('Description')"/>

                <x-text-input id="description" class="block mt-1 w-7/12" type="text" name="description"
                              :value="old('description')" required autofocus/>

                <x-input-error :messages="$errors->get('description')" class="mt-2"/>
            </div>


            <div class="mt-4">
                <x-input-label for="Book" :value="__('Book')"/>

                <x-text-input id="pdf" class="block mt-1 w-7/12" type="file" name="pdf" :value="old('pdf')" required
                              autofocus/>

                <x-input-error :messages="$errors->get('pdf')" class="mt-2"/>
            </div>

            <div class="mt-4">

                <x-input-label for="Category" :value="__('Category')"/>


                <select name="category_id" id="category_id">
                    @php
                        $categories = \App\Models\Category::all();
                    @endphp

                    @foreach ($categories as $category)
                        <option value="{{ $category->id }}">{{ $category->name }} </option>
                    @endforeach
                </select>


                <x-input-error :messages="$errors->get('category_id')" class="mt-2"/>

                <div class="mt-4">
                    <x-primary-button class="">
                        {{ __('Submit') }}
                    </x-primary-button>
                </div>
            </div>
        </form>

    </section>
</x-app-layout>
