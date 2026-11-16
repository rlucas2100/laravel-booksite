<?php

/*
| The audio and video shelves. The files are in database/seeders/media and are
| copied into storage by the seeder. An entry whose file is missing is skipped.
|
| Audio: public-domain readings from LibriVox (librivox.org), where volunteers
| read out-of-copyright books and poems and give the recordings away.
| Video: short trailers made for this shop.
*/

return [
    'audio' => [
        [
            'slug' => 'jabberwocky',
            'title' => 'Jabberwocky',
            'creator' => 'Lewis Carroll, read for LibriVox',
            'year' => 1871,
            'category' => 'plays-poetry',
            'design' => 'burst:grape',
            'file' => 'jabberwocky.mp3',
            'duration' => 90,
            'link' => 'https://archive.org/details/jabberwocky_librivox',
            'description' => '’Twas brillig, and the slithy toves did gyre and gimble in the wabe. Ninety seconds of the best nonsense in English, from Through the Looking-Glass.',
        ],
        [
            'slug' => 'ozymandias',
            'title' => 'Ozymandias',
            'creator' => 'Percy Bysshe Shelley, read for LibriVox',
            'year' => 1818,
            'category' => 'plays-poetry',
            'design' => 'sun:sun',
            'file' => 'ozymandias.mp3',
            'duration' => 74,
            'link' => 'https://archive.org/details/ozymandias_librivox',
            'description' => 'Two vast and trunkless legs of stone stand in the desert. Shelley’s fourteen lines on what becomes of the mighty.',
        ],
        [
            'slug' => 'kubla-khan',
            'title' => 'Kubla Khan',
            'creator' => 'Samuel Taylor Coleridge, read for LibriVox',
            'year' => 1816,
            'category' => 'plays-poetry',
            'design' => 'waves:mint',
            'file' => 'kubla-khan.mp3',
            'duration' => 175,
            'link' => 'https://archive.org/details/kublakhan_librivox',
            'description' => 'A pleasure-dome, a sacred river and caverns measureless to man. Coleridge said the poem came to him in a dream, and that a visitor interrupted before he could finish writing it down.',
        ],
        [
            'slug' => 'o-captain-my-captain',
            'title' => 'O Captain! My Captain!',
            'creator' => 'Walt Whitman, read for LibriVox',
            'year' => 1865,
            'category' => 'plays-poetry',
            'design' => 'stripes:cobalt',
            'file' => 'o-captain-my-captain.mp3',
            'duration' => 115,
            'link' => 'https://archive.org/details/o_captain_1408.poem_librivox',
            'description' => 'Whitman’s elegy for Abraham Lincoln. The ship has come home safe, and its captain has not.',
        ],
        [
            'slug' => 'invictus',
            'title' => 'Invictus',
            'creator' => 'W. E. Henley, read for LibriVox',
            'year' => 1888,
            'category' => 'plays-poetry',
            'design' => 'peaks:ink',
            'file' => 'invictus.mp3',
            'duration' => 65,
            'link' => 'https://archive.org/details/invictus_2305.poem_librivox',
            'description' => 'Sixteen lines about refusing to give in, written from a hospital bed. One minute long.',
        ],
    ],

    'video' => [
        [
            'slug' => 'dracula-the-trailer',
            'title' => 'Dracula: the trailer',
            'creator' => 'The Cliffhanger desk',
            'year' => 2026,
            'category' => 'gothic-horror',
            'design' => 'sun:ink',
            'file' => 'dracula-trailer.mp4',
            'duration' => 12,
            'link' => null,
            'description' => 'Twelve seconds on why you should read Bram Stoker’s Dracula tonight, with the lights on.',
        ],
        [
            'slug' => 'treasure-island-the-trailer',
            'title' => 'Treasure Island: the trailer',
            'creator' => 'The Cliffhanger desk',
            'year' => 2026,
            'category' => 'adventure',
            'design' => 'waves:sun',
            'file' => 'treasure-island-trailer.mp4',
            'duration' => 12,
            'link' => null,
            'description' => 'A map, a mutiny and a one-legged cook. The pirate story every other pirate story copied, in twelve seconds.',
        ],
        [
            'slug' => 'alice-the-trailer',
            'title' => 'Alice in Wonderland: the trailer',
            'creator' => 'The Cliffhanger desk',
            'year' => 2026,
            'category' => 'childrens',
            'design' => 'blocks:mint',
            'file' => 'alice-trailer.mp4',
            'duration' => 12,
            'link' => null,
            'description' => 'Down the rabbit hole in twelve seconds. Curiouser and curiouser.',
        ],
    ],
];
