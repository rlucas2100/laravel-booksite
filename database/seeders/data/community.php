<?php

/*
| Sample readers, their reviews and the conversations under the entries.
| The readers are made up. Reviews: [reader, stars (0-5), headline or null, text].
| Comments: [reader, text, [replies as [reader, text]]].
*/

return [
    'readers' => [
        'maeve' => 'Maeve D.',
        'tunde' => 'Tunde A.',
        'priya' => 'Priya S.',
        'ben' => 'Bookish Ben',
        'niamh' => 'Niamh K.',
        'sam' => 'Sam W.',
        'leila' => 'Leila H.',
        'jonas' => 'Jonas B.',
        'aoife' => 'Aoife R.',
        'marcus' => 'Marcus T.',
        'wen' => 'Wen L.',
        'dot' => 'Dot',
    ],

    'reviews' => [
        'pride-and-prejudice' => [
            ['maeve', 5, 'Funnier than any rom-com', 'I put this off for years because I thought it would be stiff. It’s the opposite. Mr Collins’s proposal made me laugh out loud on the bus.'],
            ['priya', 5, 'Lizzy Bennet forever', 'Every argument between Elizabeth and Darcy is a tennis match. Read it twice: once for the plot and once for the insults.'],
            ['jonas', 3, 'Good, but slow to start', 'The first fifty pages are a lot of people visiting each other. Once Darcy’s letter arrives, it flies.'],
            ['marcus', 4, null, 'Came for the film, stayed for Mr Bennet’s one-liners.'],
        ],
        'dracula' => [
            ['niamh', 5, 'Read it with the lights on', 'The diary format makes it feel real, as if you’d found the papers yourself. The ship’s log chapter is the scariest thing in the book.'],
            ['tunde', 4, 'Great first half', 'Castle Dracula is perfect. It sags a bit in the middle while everyone writes memos to each other, then the chase at the end pays it all off.'],
            ['wen', 5, 'Mina is the real hero', 'She organises the evidence, types everything up and works out the route. The men would be lost without her.'],
            ['sam', 2, 'Too many diaries', 'I liked the idea more than the reading. Every character sounds the same to me.'],
        ],
        'frankenstein' => [
            ['leila', 5, 'Not the story you think', 'No bolts, no lab assistant, no lightning. Just a sad, clever creature asking why he was made. I felt far more for him than for Victor.'],
            ['ben', 4, 'Victor is the monster', 'He makes a person and then goes to bed. Everything after that is his fault. Brilliant book.'],
            ['dot', 3, 'Heavy going in places', 'The letters at the start nearly lost me. Stick with it until the creature tells his own story.'],
        ],
        'the-adventures-of-sherlock-holmes' => [
            ['marcus', 5, 'One more case, then bed', 'Twelve stories, and I read ten of them in a weekend. The Red-Headed League has the best opening of any mystery I know.'],
            ['maeve', 5, null, 'Perfect train reading. Each case takes about half an hour.'],
            ['aoife', 4, 'Watson deserves more credit', 'Holmes gets the glory, but the stories only work because Watson is such good company.'],
        ],
        'the-hound-of-the-baskervilles' => [
            ['tunde', 5, 'The fog! The moor!', 'Atmosphere you could cut with a knife. I guessed the villain and still enjoyed every page.'],
            ['leila', 4, null, 'Holmes is missing for the middle of the book and somehow it’s better for it.'],
        ],
        'treasure-island' => [
            ['ben', 5, 'Still the best pirate story', 'Read it to my nephew and we both wanted to keep going past bedtime. Long John Silver is charming and terrifying on the same page.'],
            ['sam', 4, 'Fast and salty', 'No padding at all. The apple barrel scene had my heart going.'],
            ['priya', 3, null, 'Good fun, though the sailing terms went over my head.'],
        ],
        'moby-dick' => [
            ['jonas', 4, 'Skip nothing, even the whale chapters', 'Yes, there is a chapter about rope. Lean into it. The last three chapters are worth the whole voyage.'],
            ['dot', 1, 'I tried. Twice.', 'Call me defeated. I got to the chapter listing the types of whale and jumped ship.'],
            ['wen', 5, 'Stranger and funnier than I expected', 'Ishmael and Queequeg having to share a bed at the inn is a comedy sketch. Nobody told me this book had jokes.'],
        ],
        'the-count-of-monte-cristo' => [
            ['aoife', 5, 'A thousand pages of payback', 'The longest book I’ve ever finished, and I never once checked how many pages were left.'],
            ['niamh', 5, null, 'The prison chapters with the Abbé are the best friendship in fiction.'],
            ['marcus', 4, 'Long, and worth it', 'A few side plots wander off. When the Count finally sits down to dinner with his enemies, it’s electric.'],
        ],
        'alices-adventures-in-wonderland' => [
            ['priya', 5, 'Makes less sense every time, and I love it more', 'The Mad Tea-Party is the funniest ten pages in English.'],
            ['leila', 4, null, 'Read it aloud to a six-year-old who now answers every question with “Why is a raven like a writing-desk?”'],
            ['sam', 3, 'Clever nonsense', 'Great in small doses. I liked it best at a chapter a night.'],
        ],
        'the-picture-of-dorian-gray' => [
            ['maeve', 5, 'Every line is quotable', 'Lord Henry gets the best lines and gives the worst advice. Wilde knew exactly what he was doing.'],
            ['wen', 4, 'Gorgeous and nasty', 'Chapter eleven is a list of fabrics and jewels, and you can skim it. The rest is a slow, lovely car crash.'],
        ],
        'jane-eyre' => [
            ['aoife', 5, 'Reader, I stayed up all night', 'Jane is small, plain and poor, and she refuses to be pushed around by anyone. I’d follow her anywhere.'],
            ['ben', 4, null, 'The red-room chapter scared me more than most horror novels.'],
            ['jonas', 2, 'Rochester is a lot', 'Beautifully written, but I couldn’t warm to him, and he takes up most of the book.'],
        ],
        'the-war-of-the-worlds' => [
            ['tunde', 5, 'Feels like breaking news', 'Wells wrote this in 1898 and it reads like live coverage. The heat-ray on Horsell Common is properly frightening.'],
            ['dot', 4, null, 'Short, grim and brilliant. The ending is so simple it’s cheeky.'],
        ],
        'the-time-machine' => [
            ['marcus', 4, 'A hundred pages that started everything', 'The far-future beach near the end is one of the eeriest things I’ve read.'],
            ['niamh', 3, null, 'More of a thought experiment than a story, but a very good one.'],
        ],
        'a-christmas-carol' => [
            ['leila', 5, 'Every December', 'It takes two hours and it works every single time. The Fezziwig party is pure joy.'],
            ['priya', 5, null, 'Marley’s ghost dragging his chains up the stairs. There’s nothing else like it.'],
            ['sam', 4, 'Shorter than I thought', 'I only knew the films. The book is funnier, especially the narrator.'],
        ],
        'strange-case-of-dr-jekyll-and-mr-hyde' => [
            ['wen', 4, 'Wish I hadn’t known the twist', 'It’s written as a mystery and everybody already knows the answer. Still a tense little book.'],
            ['maeve', 3, null, 'Good, creepy, and over in an evening.'],
        ],
        'dubliners' => [
            ['niamh', 5, '“The Dead” is perfect', 'The last page, with the snow falling all over Ireland, is the best ending to a short story I know.'],
            ['aoife', 4, 'Small lives, sharply drawn', 'Nothing much happens in most of them, and you still feel it. “Araby” got me.'],
            ['jonas', 3, null, 'Quiet and a bit bleak. Read one at a time, not all in a row.'],
        ],
        'carmilla' => [
            ['leila', 4, 'Before Dracula, there was Carmilla', 'Short, dreamy and unsettling. You can almost see Stoker taking notes.'],
            ['tunde', 4, null, 'A proper gothic chiller in under a hundred pages.'],
        ],
        'the-wind-in-the-willows' => [
            ['dot', 5, 'Poop-poop!', 'Mr Toad is the best character in children’s books. I will not be taking questions.'],
            ['ben', 4, null, 'The picnic by the river is the cosiest chapter ever written.'],
        ],
        'the-importance-of-being-earnest' => [
            ['priya', 5, 'A handbag?', 'Read it in an hour and laughed the whole way through. Lady Bracknell should be on banknotes.'],
            ['marcus', 4, null, 'All plot, no fat, every line a joke.'],
        ],
        'around-the-world-in-eighty-days' => [
            ['sam', 4, 'A Victorian speedrun', 'Fogg never loses his temper once. Passepartout does all the panicking for him.'],
        ],
        'the-secret-garden' => [
            ['aoife', 5, null, 'Made me want to go outside and plant something. Dickon is a delight.'],
        ],
        'meditations' => [
            ['jonas', 4, 'Two-minute chapters', 'I keep it by the kettle and read a paragraph while it boils. Half of it is him telling himself to get out of bed.'],
            ['wen', 0, 'Couldn’t finish it', 'Maybe it’s this old translation, but the thees and thous beat me. Zero stars for the experience. Sorry, Marcus.'],
        ],
        'walden' => [
            ['maeve', 3, 'Lovely sentences, smug neighbour', 'The pond chapters are beautiful. The lectures about my spending habits, less so.'],
        ],
        'twenty-thousand-leagues-under-the-sea' => [
            ['tunde', 4, null, 'Captain Nemo is the coolest character of the 1800s. Some of the lists of fish go on a bit.'],
        ],
        'romeo-and-juliet' => [
            ['leila', 4, 'Faster than you remember', 'Four days from the first glance to the tomb. Mercutio steals every scene he’s in.'],
        ],
        'emma' => [
            ['priya', 4, 'Emma is a nightmare and I adore her', 'She gets everything wrong with total confidence. Mr Knightley telling her off at Box Hill is the best scene.'],
        ],
        'the-moonstone' => [
            ['marcus', 4, 'The butler narrates, and he’s brilliant', 'Betteredge turning to Robinson Crusoe for life advice is a great running joke.'],
        ],
        'the-wonderful-wizard-of-oz' => [
            ['dot', 4, null, 'Darker and odder than the film. The Tin Woodman’s back story!'],
        ],

        // Audio
        'jabberwocky' => [
            ['priya', 5, 'Frabjous', 'Ninety seconds well spent. “Snicker-snack” is a great pair of words.'],
            ['dot', 4, null, 'Played it to the kids at bedtime. Mistake. Nobody slept.'],
        ],
        'ozymandias' => [
            ['marcus', 5, 'Look on my works', 'A minute long, and it stays with you all day.'],
            ['niamh', 4, null, 'A lovely clear reading.'],
        ],
        'kubla-khan' => [
            ['leila', 4, null, 'Hypnotic. Shame about the person from Porlock.'],
            ['tunde', 3, 'Odd one', 'Beautiful sounds. No idea what happened.'],
        ],
        'o-captain-my-captain' => [
            ['maeve', 4, null, 'Gets me every time at “fallen cold and dead”.'],
        ],
        'invictus' => [
            ['jonas', 5, 'One minute of backbone', 'I play this before job interviews.'],
            ['wen', 4, null, 'Short and stirring.'],
        ],

        // Video
        'dracula-the-trailer' => [
            ['niamh', 4, null, 'Sold. Starting it tonight.'],
            ['sam', 3, 'Fun, but short', 'More of these, please, and a bit longer.'],
        ],
        'treasure-island-the-trailer' => [
            ['ben', 5, 'Yo ho ho', 'Showed this to my class before we started the book. It worked.'],
        ],
        'alice-the-trailer' => [
            ['priya', 4, null, 'Curiouser and curiouser. Cute.'],
        ],

        // Blog
        'five-classics-that-read-like-thrillers' => [
            ['sam', 4, 'Good list', 'I’d add The Moonstone. Otherwise spot on.'],
            ['ben', 5, null, 'Read Monte Cristo because of this. No regrets.'],
        ],
        'what-the-stars-mean' => [
            ['aoife', 5, 'Finally, a zero', 'Other sites should copy this scale.'],
        ],
        'why-everything-here-is-free' => [
            ['tunde', 4, null, 'A clear explanation. I never knew how LibriVox worked.'],
        ],
        'how-to-read-more-without-trying-very-hard' => [
            ['dot', 4, 'Quitting is allowed!', 'Tip two changed my reading life.'],
            ['priya', 3, null, 'Sensible, though nothing I hadn’t heard before.'],
        ],
    ],

    'comments' => [
        'dracula' => [
            ['sam', 'Is it worth reading Carmilla first?', [
                ['leila', 'Yes! It’s short, and you’ll spot what Stoker borrowed.'],
                ['niamh', 'Either order works. Carmilla is the quicker read.'],
            ]],
            ['wen', 'The diary entries are dated, so you can read it “in real time” starting on 3 May. A few of us did it last year.', []],
        ],
        'pride-and-prejudice' => [
            ['jonas', 'Which screen version do people like best alongside the book?', [
                ['maeve', 'The 1995 series for the dialogue. It keeps most of Austen’s lines.'],
                ['marcus', 'Controversial, but I like them both.'],
            ]],
        ],
        'the-count-of-monte-cristo' => [
            ['ben', 'How long did this take everyone? I’m on week three.', [
                ['aoife', 'About a month, a few chapters a night. It never dragged.'],
            ]],
        ],
        'moby-dick' => [
            ['dot', 'Tell me honestly: can I skip the whale biology?', [
                ['jonas', 'You can, but the rope chapter is secretly great.'],
                ['wen', 'Skim them the first time. Nobody will know.'],
            ]],
        ],
        'alices-adventures-in-wonderland' => [
            ['priya', 'Does anyone know the answer to the raven riddle?', [
                ['leila', 'Carroll said there wasn’t one. He made one up years later because people kept asking.'],
            ]],
        ],
        'frankenstein' => [
            ['ben', 'A reminder that the creature has no name. Frankenstein is the man who made him.', [
                ['dot', 'And he isn’t a doctor either. He’s a student who never finished.'],
            ]],
        ],
        'the-adventures-of-sherlock-holmes' => [
            ['maeve', 'Best story in the collection? I say The Speckled Band.', [
                ['marcus', 'The Red-Headed League for me.'],
                ['aoife', 'A Scandal in Bohemia. Irene Adler wins.'],
            ]],
        ],
        'treasure-island' => [
            ['sam', 'Reading this to my class next term. Any tips?', [
                ['ben', 'Do the voices. Silver needs a big one.'],
            ]],
        ],
        'a-christmas-carol' => [
            ['niamh', 'The annual reread starts on 1 December. Who’s in?', [
                ['leila', 'Every year.'],
                ['priya', 'In. One stave a week.'],
            ]],
        ],
        'dubliners' => [
            ['aoife', 'For anyone visiting Dublin: you can still walk most of the routes in these stories.', []],
        ],
        'the-wind-in-the-willows' => [
            ['dot', 'Team Toad or Team Badger?', [
                ['ben', 'Badger. Somebody has to be the adult.'],
            ]],
        ],
        'what-the-stars-mean' => [
            ['jonas', 'Does a zero count towards the average?', [
                ['desk', 'It does. A zero pulls the average down like any other score.'],
            ]],
        ],
        'kubla-khan' => [
            ['marcus', 'Who was the person from Porlock, though?', [
                ['leila', 'Nobody knows. Some people think Coleridge made the visitor up as an excuse.'],
            ]],
        ],
    ],
];
