import Alpine from 'alpinejs';

/*
| The small pieces of behaviour on the site, as Alpine.js components.
| Each is switched on in a Blade view with x-data="name(...)".
*/

/** The sun/moon button in the header. The choice is remembered in this browser. */
Alpine.data('themeToggle', () => ({
    dark: document.documentElement.classList.contains('dark'),

    toggle() {
        this.dark = !this.dark;
        document.documentElement.classList.toggle('dark', this.dark);
        try {
            localStorage.setItem('theme', this.dark ? 'dark' : 'light');
        } catch (e) {
            // private browsing: the theme just won't be remembered
        }
    },
}));

/**
 * The 0 to 5 star picker on the review form. The stars are real radio buttons,
 * so the arrow keys work; this adds the hover preview, the words for each
 * score and the mascot's mood.
 */
Alpine.data('starPicker', (initial, words) => ({
    value: initial,
    hover: null,
    popping: false,

    get shown() {
        return this.hover ?? this.value;
    },

    get word() {
        return this.shown === null ? 'Tap a star (or the zero)' : words[this.shown];
    },

    lit(stars) {
        return this.shown !== null && stars <= this.shown;
    },

    pick(stars) {
        this.value = stars;
        this.popping = false;
        this.$nextTick(() => (this.popping = true));
    },
}));

/** The cassette player for audio entries. Remembers where you stopped, per recording. */
Alpine.data('audioPlayer', (key) => ({
    playing: false,
    current: 0,
    duration: 0,
    rate: 1,
    failed: false,

    init() {
        const audio = this.$refs.audio;
        const saved = Number(this.recall());

        audio.addEventListener('loadedmetadata', () => {
            this.duration = audio.duration;
            if (saved > 3 && saved < audio.duration - 3) {
                audio.currentTime = saved;
            }
        });
        audio.addEventListener('timeupdate', () => {
            this.current = audio.currentTime;
            this.remember(audio.currentTime);
        });
        audio.addEventListener('play', () => (this.playing = true));
        audio.addEventListener('pause', () => (this.playing = false));
        audio.addEventListener('ended', () => {
            this.playing = false;
            this.remember(0);
        });
        audio.addEventListener('error', () => (this.failed = true));

        if (audio.readyState >= 1) {
            this.duration = audio.duration;
        }
    },

    toggle() {
        const audio = this.$refs.audio;
        audio.paused ? audio.play().catch(() => (this.failed = true)) : audio.pause();
    },

    skip(seconds) {
        const audio = this.$refs.audio;
        audio.currentTime = Math.min(Math.max(0, audio.currentTime + seconds), this.duration || 0);
    },

    seek(event) {
        this.$refs.audio.currentTime = Number(event.target.value);
    },

    faster() {
        const speeds = [1, 1.25, 1.5, 2, 0.75];
        this.rate = speeds[(speeds.indexOf(this.rate) + 1) % speeds.length];
        this.$refs.audio.playbackRate = this.rate;
    },

    get percent() {
        return this.duration ? (this.current / this.duration) * 100 : 0;
    },

    clock(seconds) {
        if (!Number.isFinite(seconds)) return '0:00';
        const whole = Math.floor(seconds);
        return Math.floor(whole / 60) + ':' + String(whole % 60).padStart(2, '0');
    },

    recall() {
        try {
            return localStorage.getItem('listen:' + key) || 0;
        } catch (e) {
            return 0;
        }
    },

    remember(seconds) {
        try {
            localStorage.setItem('listen:' + key, String(Math.floor(seconds)));
        } catch (e) {
            // not remembered, that's all
        }
    },
}));

/**
 * The upload form. Picking a type (book, video, audio, article) changes which
 * fields show and which files are accepted. Files can be dropped onto the
 * boxes, are checked for size before the form is sent, and a video or audio
 * file's length is read here in the browser.
 */
Alpine.data('entryForm', (config) => ({
    type: config.type,
    types: config.types,
    coverLimit: config.coverLimit,

    file: null,
    fileError: '',
    duration: config.duration || '',
    cover: null,
    coverPreview: null,
    coverError: '',
    over: null,

    get kind() {
        return this.types[this.type];
    },

    switchTo(type) {
        if (this.type === type) return;
        this.type = type;
        this.clearFile();
    },

    megabytes(bytes) {
        return (bytes / 1048576).toFixed(bytes < 10485760 ? 1 : 0) + ' MB';
    },

    extension(name) {
        return name.includes('.') ? name.split('.').pop().toLowerCase() : '';
    },

    chooseFile(event) {
        this.checkFile(event.target.files[0]);
    },

    dropFile(event) {
        this.over = null;
        if (!event.dataTransfer.files.length) return;
        this.$refs.file.files = event.dataTransfer.files;
        this.checkFile(event.dataTransfer.files[0]);
    },

    checkFile(file) {
        this.fileError = '';
        this.duration = '';
        this.file = file || null;
        if (!file) return;

        if (!this.kind.extensions.includes(this.extension(file.name))) {
            this.fileError = 'That file type isn’t accepted here. Use: ' + this.kind.extensions.join(', ') + '.';
        } else if (file.size > this.kind.limit * 1024) {
            this.fileError = 'That file is ' + this.megabytes(file.size) + '. The limit is ' + this.megabytes(this.kind.limit * 1024) + '.';
        }

        if (this.fileError) {
            this.$refs.file.value = '';
            this.file = null;
            return;
        }

        // Ask the browser how long the recording is.
        if (this.type === 'video' || this.type === 'audio') {
            const media = document.createElement(this.type);
            const url = URL.createObjectURL(file);
            media.preload = 'metadata';
            media.onloadedmetadata = () => {
                if (Number.isFinite(media.duration)) this.duration = Math.max(1, Math.round(media.duration));
                URL.revokeObjectURL(url);
            };
            media.onerror = () => URL.revokeObjectURL(url);
            media.src = url;
        }
    },

    clearFile() {
        this.file = null;
        this.fileError = '';
        this.duration = '';
        if (this.$refs.file) this.$refs.file.value = '';
    },

    chooseCover(event) {
        this.checkCover(event.target.files[0]);
    },

    dropCover(event) {
        this.over = null;
        if (!event.dataTransfer.files.length) return;
        this.$refs.cover.files = event.dataTransfer.files;
        this.checkCover(event.dataTransfer.files[0]);
    },

    checkCover(file) {
        this.coverError = '';
        if (this.coverPreview) URL.revokeObjectURL(this.coverPreview);
        this.coverPreview = null;
        this.cover = file || null;
        if (!file) return;

        if (!['jpg', 'jpeg', 'png', 'webp'].includes(this.extension(file.name))) {
            this.coverError = 'The cover must be a JPG, PNG or WebP picture.';
        } else if (file.size > this.coverLimit * 1024) {
            this.coverError = 'That picture is ' + this.megabytes(file.size) + '. The limit is ' + this.megabytes(this.coverLimit * 1024) + '.';
        }

        if (this.coverError) {
            this.$refs.cover.value = '';
            this.cover = null;
            return;
        }

        this.coverPreview = URL.createObjectURL(file);
    },

    clearCover() {
        if (this.coverPreview) URL.revokeObjectURL(this.coverPreview);
        this.cover = null;
        this.coverPreview = null;
        this.coverError = '';
        this.$refs.cover.value = '';
    },
}));

/** A text box with a "123 / 1500" counter under it. */
Alpine.data('counted', (limit) => ({
    limit,
    used: 0,

    init() {
        this.used = this.$refs.text.value.length;
    },

    count() {
        this.used = this.$refs.text.value.length;
    },
}));

/** Any form with data-confirm="Are you sure?" asks before it is sent. */
document.addEventListener('submit', (event) => {
    const question = event.target.dataset ? event.target.dataset.confirm : null;

    if (question && !window.confirm(question)) {
        event.preventDefault();
    }
});

window.Alpine = Alpine;
Alpine.start();
