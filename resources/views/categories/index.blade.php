<x-app-layout>
    <x-slot name="header">
    </x-slot>
    <h1 class="mx-6 my-6 text-2xl text-center">Admin edit all categories</h1>

    {{--    new design--}}
    <div class="relative overflow-x-auto shadow-md sm:rounded-lg">
        <table class="mx-auto w-4/5 text-sm text-left text-gray-500 dark:text-gray-400">
            <thead class="text-xs text-gray-700 uppercase bg-gray-50 dark:bg-gray-700 dark:text-gray-400">
            <tr>
                <th scope="col" class="px-6 py-3">
                    Category Name
                </th>
                <th scope="col" class="px-6 py-3">
                    Action
                </th>
                <th scope="col" class="px-6 py-3">
                    <a href="{{ route('categories.create') }}"
                    <button type="button"
                            class="px-3 py-2 text-xs font-medium text-center text-white bg-blue-700 rounded-lg hover:bg-blue-800 focus:ring-4 focus:outline-none focus:ring-blue-300 dark:bg-blue-600 dark:hover:bg-blue-700 dark:focus:ring-blue-800">
                        New Category
                    </button>
                    </a>
                </th>

            </tr>
            </thead>
            <tbody>
            @foreach ($categories as $category)

                <tr class="bg-white border-b dark:bg-gray-800 dark:border-gray-700">
                    <th scope="row" class="px-6 py-4 font-medium text-gray-900 whitespace-nowrap dark:text-white">
                        <a href="{{ route('categories.show', $category) }}">
                            {{ $category->name }}
                        </a>
                    </th>
                    <td class="px-6 py-4">
                        <a href="{{ route('categories.edit', $category) }}"
                           class="btn-link ml-auto ">Edit</a>
                    </td>
                </tr>
            @endforeach

            </tbody>
        </table>


    </div>
    {{--    end new design--}}


</x-app-layout>

