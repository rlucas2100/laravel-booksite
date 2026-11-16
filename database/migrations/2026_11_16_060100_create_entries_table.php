<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * One table for everything on the shelves. "type" says whether a row is a
     * book, a video, an audio episode or an article (see App\Enums\EntryType).
     * Columns a type doesn't use are simply left empty.
     */
    public function up(): void
    {
        Schema::create('entries', function (Blueprint $table) {
            $table->id();
            // entries are deleted when the user who uploaded them is deleted
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('category_id')->constrained()->restrictOnDelete();

            $table->string('type', 16)->index();           // book | video | audio | article
            $table->string('title');
            $table->string('slug')->unique();
            $table->string('creator');                     // author, film-maker, reader/host or writer
            $table->text('description');
            $table->longText('body')->nullable();          // an article's text (Markdown) or a book's opening lines
            $table->unsignedSmallInteger('year')->nullable();
            $table->string('link')->nullable();            // where to find out more / read the full text

            $table->string('cover_path')->nullable();      // an uploaded cover image; without one a cover is drawn
            $table->string('cover_design', 40)->nullable(); // "motif:palette" to pin the drawn cover's look

            $table->string('file_path')->nullable();       // the PDF, video or audio file
            $table->unsignedBigInteger('file_size')->nullable();
            $table->unsignedInteger('duration')->nullable(); // seconds, for video and audio

            $table->boolean('featured')->default(false);
            $table->string('sandbox', 32)->nullable()->index();
            $table->timestamp('published_at')->nullable()->index();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('entries');
    }
};
