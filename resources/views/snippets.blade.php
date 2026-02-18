$comment = new Comment([
'body' => 'this is another test',
'user_id' => 10
]);

$book = Book::find(23);

foreach ($book->comments[0]->user->comments as $comment) {
echo $comment->body.'<br>';
}
dd($book->comments[0]->user->comments);

//        $podcast->comments()->save($comment);

dd($comment, $podcast);

