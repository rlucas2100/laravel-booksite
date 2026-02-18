<?php

namespace Database\Factories;
use App\Models\Book;
use App\Models\Category;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Book>
 */
class BookFactory extends Factory
{
    protected $model = Book::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition()
    {
//        use category factory to create 10 random categories upon seeding


      $title = $this->bookTitle(4);
      $slug = Str::slug($title);
        return [
            'category_id' => rand(1,10),
            'title' => $title,
            'slug' => $slug,
            'uuid' => $this->faker->randomNumber(),

//            'thumbnail' =>  'thumbnails/'.'AuiJniCXrtbB6LJq740h8OfS7DFugJUOvvZzoIct.png',
            'thumbnail' =>  'thumbnails/'.$this->faker->image(null, 360, 360, 'animals', true),
            'description' => $this->faker->sentence,
            'pdf' => 'pdfs/'.'ZmB5Qy6vHAAp4Z03o4i2GxgoSTWUAE1mRk6SmPqW.pdf'

        ];
    }

//    custom function to return a book title
    public function bookTitle($nbWords): string
    {
      $sentence = $this->faker->sentence($nbWords);
      return substr($sentence, 0, strlen($sentence) - 1);
    }
}
