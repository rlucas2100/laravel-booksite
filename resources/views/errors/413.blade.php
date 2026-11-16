@include('errors.page', [
    'code' => 413,
    'mood' => 0,
    'heading' => 'That file is too big for this server',
    'text' => 'The upload was larger than the server accepts in one go. Go back and choose a smaller file.',
])
