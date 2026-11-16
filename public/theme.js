// Runs before the page is drawn, so a dark-mode reader never sees a flash of the light theme.
(function () {
    try {
        var saved = localStorage.getItem('theme');
        var dark = saved ? saved === 'dark' : window.matchMedia('(prefers-color-scheme: dark)').matches;
        if (dark) document.documentElement.classList.add('dark');
    } catch (e) {}
})();
