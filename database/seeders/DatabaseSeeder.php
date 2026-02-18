<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use App\Models\User;
use App\Models\Book;
use App\Models\Category;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
//        use fake data for everything except the pdf field
//        Book::factory(5)->create([
//            'pdf' => 'test.pdf'
//        ]);



//        calling additional seed classes
        $this->call([
            CategorySeeder::class,
            UserSeeder::class,
            PodcastSeeder::class,
            RoleAndPermissionSeeder::class
        ]);


//        insert 10 categories to db
//        $fiction = Category::create([
//           'name' => 'Fiction',
//

//      Category::truncate();
//      User::truncate();
//      Book::truncate();

//      Category::factory(10)->create();

      // create a new user that has between 0-3 books associated with them
//      $user = User::factory(20)
//        ->has(Book::factory()->count(rand(0,5)))
//        ->create();

//       experiment with this
//      Book::factory()->create([
//        'user_id'   => $users->random()->id,
//        'category_id'   => $categories->random()->id
//      ]);
//



//create test controller and test route and expieriment with eloquient queries where users have no books
//  eloquent relation wherehas opposite where hasnt use DD in test controller no need for view


//
//        this creates a corresponding book, category and user
//        Book::factory(2)->create();
//
//
//        $books = Book::factory()
//            ->count(3)
//            ->for($user)
//            ->create();
//
//
//        User::factory()
//            ->count(2)
//            ->hasBooks(1)
//            ->create();

//         \App\Models\User::factory()->create();

//        $user = \App\Models\User::factory(10)->create();



        // \App\Models\User::factory()->create([
        //     'name' => 'Test User',
        //     'email' => 'test@example.com',
        // ]);
    }
}
