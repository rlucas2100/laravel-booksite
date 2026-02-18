<x-app-layout>
    <x-slot name="header">

    </x-slot>

    <section class="">
        <form class="rounded px-8 pt-6 pb-8 mb-4" method="POST" action="{{route('categories.store')}}"
              enctype="multipart/form-data">
            @csrf

            <div class="mb-6">

                <x-input-label for="name" :value="__('New Category Name')"/>

                <x-text-input id="name" class="block mt-1 w-2/5" type="text" name="name" :value="old('name')" required
                              autofocus/>

                <x-input-error :messages="$errors->get('name')" class="mt-2"/>

            </div>

            <x-primary-button class="">
                {{ __('Submit') }}
            </x-primary-button>

        </form>
    </section>
</x-app-layout>
