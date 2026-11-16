@include('errors.page', [
    'code' => 403,
    'mood' => 2,
    'heading' => 'That’s not yours to open',
    'text' => $exception->getMessage() && $exception->getMessage() !== 'This action is unauthorized.'
        ? $exception->getMessage()
        : 'You don’t have permission to do that. If this is the public demo, the shop’s own books, members and reviews are locked so the next visitor finds them intact.',
])
