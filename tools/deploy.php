<?php

/*
|--------------------------------------------------------------------------
| Build a folder that can be uploaded to shared hosting by FTP
|--------------------------------------------------------------------------
|
|   php tools/deploy.php "C:\path\to\website\bookshop" https://example.com/bookshop
|
| Shared hosting usually has no command line, can't point a domain at
| Laravel's /public folder and can't run "composer install". So this makes one
| self-contained folder to upload as it is:
|
|   bookshop/
|     index.php      the front controller (checks the PHP version first)
|     .htaccess      sends every address to index.php, blocks the app folder
|     build/         the compiled CSS, JS and fonts
|     laravel/       the application and its vendor folder; never served
|
| On the server the database, sessions, logs and uploads are kept in a
| "bookshop-data" folder NEXT TO the website folder when that is possible, so
| uploading a new version never touches them. The site installs itself on the
| first visit (SHOP_AUTO_INSTALL).
|
| The settings for the live site (its key, the owner's login) are remembered
| in .env.deploy, which is never committed.
|
*/

$root = dirname(__DIR__);
chdir($root);

$say = fn (string $line) => fwrite(STDOUT, $line.PHP_EOL);
$fail = function (string $line): never {
    fwrite(STDERR, 'STOPPED: '.$line.PHP_EOL);
    exit(1);
};
$run = function (string $command, ?string $in = null) use ($fail): void {
    $here = getcwd();
    if ($in) {
        chdir($in);
    }
    passthru($command, $code);
    chdir($here);
    if ($code !== 0) {
        $fail('"'.$command.'" failed.');
    }
};

/* ------------------------------------------------------------------ settings */

$settingsFile = $root.'/.env.deploy';
$settings = is_file($settingsFile) ? (parse_ini_file($settingsFile, false, INI_SCANNER_RAW) ?: []) : [];

$target = $argv[1] ?? ($settings['DEPLOY_TARGET'] ?? null);
$url = rtrim($argv[2] ?? ($settings['APP_URL'] ?? ''), '/');

if (! $target || ! $url) {
    $fail('Say where to build and the address it will have: php tools/deploy.php "C:\path\to\site\bookshop" https://example.com/bookshop');
}

$target = rtrim(str_replace('\\', '/', $target), '/');
$folder = basename($target);
$path = rtrim((string) parse_url($url, PHP_URL_PATH), '/') ?: '/';

if (! is_dir(dirname($target))) {
    $fail('The folder '.dirname($target).' does not exist.');
}
if (is_dir($target) && ! is_file($target.'/laravel/artisan') && count(scandir($target)) > 2) {
    $fail($target.' exists and is not an earlier build of this site. Choose another folder.');
}

$newPassword = null;

if (empty($settings['APP_KEY'])) {
    $settings['APP_KEY'] = 'base64:'.base64_encode(random_bytes(32));
}
if (empty($settings['OWNER_EMAIL'])) {
    $settings['OWNER_EMAIL'] = 'owner@example.com';
}
if (empty($settings['OWNER_PASSWORD_HASH'])) {
    // Four random words' worth of letters: easy to type, hard to guess. Shown once, stored only as a hash.
    $newPassword = implode('-', array_map(fn () => substr(bin2hex(random_bytes(4)), 0, 5), range(1, 4)));
    $settings['OWNER_PASSWORD_HASH'] = password_hash($newPassword, PASSWORD_BCRYPT, ['cost' => 12]);
}

$settings = [
    'DEPLOY_TARGET' => $target,
    'APP_URL' => $url,
    'APP_NAME' => $settings['APP_NAME'] ?? 'Cliffhanger',
    'APP_KEY' => $settings['APP_KEY'],
    'OWNER_NAME' => $settings['OWNER_NAME'] ?? 'AstroQuack',
    'OWNER_EMAIL' => $settings['OWNER_EMAIL'],
    'OWNER_PASSWORD_HASH' => $settings['OWNER_PASSWORD_HASH'],
    'SHOP_DEMO' => $settings['SHOP_DEMO'] ?? 'true',
    'SHOP_HOME_URL' => $settings['SHOP_HOME_URL'] ?? '',
    'SHOP_SOURCE_URL' => $settings['SHOP_SOURCE_URL'] ?? '',
    'STRIPE_SECRET' => $settings['STRIPE_SECRET'] ?? '',                  // a TEST key (sk_test_...) for the demo
    'STRIPE_WEBHOOK_SECRET' => $settings['STRIPE_WEBHOOK_SECRET'] ?? '',  // whsec_..., from the webhook endpoint in Stripe
];

$lines = "# Settings for the live site, written by tools/deploy.php. Keep this file private.\n";
foreach ($settings as $key => $value) {
    $lines .= $key.'="'.$value."\"\n";
}
file_put_contents($settingsFile, $lines);

/* ------------------------------------------------------------------ build */

$say('1/5  Compiling the CSS and JavaScript...');
$run('npm run build --silent');

$say('2/5  Clearing '.$target.' ...');
$remove = function (string $dir) use (&$remove): void {
    if (! is_dir($dir)) {
        return;
    }
    foreach (scandir($dir) as $item) {
        if ($item === '.' || $item === '..') {
            continue;
        }
        $full = $dir.'/'.$item;
        is_dir($full) && ! is_link($full) ? $remove($full) : unlink($full);
    }
    rmdir($dir);
};
$copy = function (string $from, string $to) use (&$copy): void {
    if (is_dir($from)) {
        @mkdir($to, 0777, true);
        foreach (scandir($from) as $item) {
            if ($item !== '.' && $item !== '..') {
                $copy($from.'/'.$item, $to.'/'.$item);
            }
        }
    } else {
        @mkdir(dirname($to), 0777, true);
        copy($from, $to);
    }
};

$remove($target);
$app = $target.'/laravel';
mkdir($app, 0777, true);

$say('3/5  Copying the application...');
foreach (['app', 'bootstrap/app.php', 'bootstrap/providers.php', 'config', 'database/migrations', 'database/seeders', 'resources/views', 'routes', 'artisan', 'composer.json', 'composer.lock'] as $item) {
    $copy($root.'/'.$item, $app.'/'.$item);
}
foreach (['bootstrap/cache', 'storage/app/private', 'storage/framework/cache/data', 'storage/framework/sessions', 'storage/framework/views', 'storage/logs'] as $dir) {
    mkdir($app.'/'.$dir, 0777, true);
    file_put_contents($app.'/'.$dir.'/.gitkeep', '');
}

$say('4/5  Installing the PHP packages the live site needs (no development tools)...');
$run('composer install --no-dev --optimize-autoloader --no-interaction --no-progress --quiet', $app);

/* ------------------------------------------------------------------ the public side */

$say('5/5  Writing the front controller and settings...');
$copy($root.'/public/build', $target.'/build');
foreach (['favicon.svg', 'theme.js'] as $file) {
    copy($root.'/public/'.$file, $target.'/'.$file);
}

$host = parse_url($url, PHP_URL_HOST);
$secure = str_starts_with($url, 'https://');

file_put_contents($app.'/.env', implode("\n", [
    '# Written by tools/deploy.php. Never share this file.',
    'APP_NAME="'.$settings['APP_NAME'].'"',
    'APP_ENV=production',
    'APP_KEY='.$settings['APP_KEY'],
    'APP_DEBUG=false',
    'APP_URL='.$url,
    '',
    'LOG_CHANNEL=single',
    'LOG_LEVEL=warning',
    'BCRYPT_ROUNDS=12',
    '',
    '# The database file is chosen by index.php (a folder outside the website when possible).',
    'DB_CONNECTION=sqlite',
    '',
    'SESSION_DRIVER=file',
    'SESSION_LIFETIME=120',
    'SESSION_PATH='.$path,
    'SESSION_SECURE_COOKIE='.($secure ? 'true' : 'false'),
    'CACHE_STORE=file',
    'QUEUE_CONNECTION=sync',
    'FILESYSTEM_DISK=local',
    'MAIL_MAILER=log',
    '',
    'SHOP_DEMO='.$settings['SHOP_DEMO'],
    'SHOP_AUTO_INSTALL=true',
    'OWNER_NAME="'.$settings['OWNER_NAME'].'"',
    'OWNER_EMAIL='.$settings['OWNER_EMAIL'],
    "OWNER_PASSWORD_HASH='".$settings['OWNER_PASSWORD_HASH']."'",
    'SHOP_HOME_URL='.$settings['SHOP_HOME_URL'],
    'SHOP_SOURCE_URL='.$settings['SHOP_SOURCE_URL'],
    'STRIPE_SECRET='.$settings['STRIPE_SECRET'],
    'STRIPE_WEBHOOK_SECRET='.$settings['STRIPE_WEBHOOK_SECRET'],
    '',
]));

// Nothing inside laravel/ is ever served, even if a rule above it is lost.
file_put_contents($app.'/.htaccess', "# The application's code, settings and data. Never served.\nRequire all denied\n");

$https = $secure ? <<<'HTACCESS'

    # Always https, always the one host name
    RewriteCond %{SERVER_PORT} !=443 [OR]
    RewriteCond %{HTTP_HOST} !^{HOST}$ [NC]
    RewriteRule ^(.*)$ {URL}/$1 [R=301,L]

HTACCESS : '';

file_put_contents($target.'/.htaccess', str_replace(['{HOST}', '{URL}', '{BASE}'], [preg_quote((string) $host), $url, rtrim($path, '/').'/'], <<<HTACCESS
# {$settings['APP_NAME']}: a Laravel site in one folder (built by tools/deploy.php)
Options -Indexes
<IfModule mod_negotiation.c>
    Options -MultiViews
</IfModule>

<IfModule mod_rewrite.c>
    RewriteEngine On
    # Says where this folder sits in the address. Some hosts (IONOS) can't work it out and answer 404 without it.
    RewriteBase {BASE}
{$https}
    # The application folder and hidden files are never served
    RewriteRule ^laravel(/|$) - [F,L]
    RewriteRule (^|/)\. - [F,L]

    # Pass the Authorization header on to PHP
    RewriteCond %{HTTP:Authorization} .
    RewriteRule .* - [E=HTTP_AUTHORIZATION:%{HTTP:Authorization}]

    # /books/ -> /books
    RewriteCond %{REQUEST_FILENAME} !-d
    RewriteCond %{REQUEST_URI} (.+)/$
    RewriteRule ^ %1 [L,R=301]

    # Everything that is not a real file goes to Laravel
    RewriteCond %{REQUEST_FILENAME} !-d
    RewriteCond %{REQUEST_FILENAME} !-f
    RewriteRule ^ index.php [L]
</IfModule>

# The compiled CSS, JS and fonts have a fingerprint in their names, so browsers may keep them
<IfModule mod_headers.c>
    <FilesMatch "\.(css|js|woff2)$">
        Header set Cache-Control "public, max-age=31536000, immutable"
    </FilesMatch>
    <FilesMatch "^theme\.js$">
        Header set Cache-Control "public, max-age=3600"
    </FilesMatch>
</IfModule>

HTACCESS));

$dataFolder = $folder.'-data';

file_put_contents($target.'/index.php', <<<PHP
<?php

/*
| The front controller for shared hosting (built by tools/deploy.php).
| Written so that even a very old PHP can read it and say what is wrong.
*/

function shop_stop(\$title, \$text)
{
    http_response_code(503);
    header('Content-Type: text/html; charset=utf-8');
    echo '<!doctype html><meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1"><title>', \$title, '</title>',
        '<body style="font-family: system-ui, sans-serif; background: #fff7e8; color: #17151c; max-width: 40rem; margin: 12vh auto; padding: 0 1.5rem; line-height: 1.6">',
        '<h1>', \$title, '</h1><p>', \$text, '</p>';
    exit;
}

if (PHP_VERSION_ID < 80300) {
    shop_stop('This site needs PHP 8.3 or newer', 'The server is running PHP ' . PHP_VERSION . '. In the hosting control panel, set the PHP version for this website to 8.3 or newer, then reload this page.');
}

\$missing = array();
foreach (array('pdo_sqlite', 'mbstring', 'openssl', 'fileinfo', 'ctype', 'dom', 'tokenizer', 'xml', 'session') as \$extension) {
    if (! extension_loaded(\$extension)) {
        \$missing[] = \$extension;
    }
}
if (\$missing) {
    shop_stop('A PHP extension is missing', 'Please switch on: <b>' . implode(', ', \$missing) . '</b> for this website, then reload this page.');
}

define('LARAVEL_START', microtime(true));

\$base = __DIR__ . '/laravel';

if (! is_file(\$base . '/vendor/autoload.php')) {
    shop_stop('The upload is not complete', 'The folder <b>laravel/vendor</b> is missing. Upload the whole folder again and reload this page.');
}

// Where the database, sessions, logs and uploads live: a folder next to the website (so it is never
// served and survives a new upload), or inside laravel/ (also never served) when that is not allowed.
\$root = isset(\$_SERVER['DOCUMENT_ROOT']) ? rtrim(str_replace('\\\\', '/', \$_SERVER['DOCUMENT_ROOT']), '/') : '';
\$places = array();
if (\$root !== '') {
    \$places[] = dirname(\$root) . '/{$dataFolder}';
}
\$places[] = \$base . '/storage';

\$data = null;
foreach (\$places as \$place) {
    if ((is_dir(\$place) || @mkdir(\$place, 0755, true)) && is_writable(\$place)) {
        \$data = \$place;
        break;
    }
}
if (\$data === null) {
    shop_stop('The site cannot save anything', 'PHP is not allowed to write to <b>laravel/storage</b>. Give that folder write permission (755 or 775), then reload this page.');
}

foreach (array('app/private', 'framework/cache/data', 'framework/sessions', 'framework/views', 'logs') as \$folder) {
    if (! is_dir(\$data . '/' . \$folder)) {
        @mkdir(\$data . '/' . \$folder, 0755, true);
    }
}
if (! is_file(\$data . '/.htaccess')) {
    @file_put_contents(\$data . '/.htaccess', "Require all denied\\n");
}

\$database = \$data . '/database.sqlite';
putenv('DB_DATABASE=' . \$database);
\$_ENV['DB_DATABASE'] = \$_SERVER['DB_DATABASE'] = \$database;

require \$base . '/vendor/autoload.php';

\$app = require_once \$base . '/bootstrap/app.php';
\$app->usePublicPath(__DIR__);
\$app->useStoragePath(\$data);

\$app->handleRequest(Illuminate\\Http\\Request::capture());

PHP);

/* ------------------------------------------------------------------ report */

$files = 0;
$bytes = 0;
foreach (new RecursiveIteratorIterator(new RecursiveDirectoryIterator($target, FilesystemIterator::SKIP_DOTS)) as $file) {
    $files++;
    $bytes += $file->getSize();
}

$say('');
$say('Built '.$target);
$say('  '.number_format($files).' files, '.number_format($bytes / 1048576, 1).' MB');
$say('  Upload the whole "'.$folder.'" folder into the website folder on the server; it will answer at '.$url.'/');
$say('  Owner login: '.$settings['OWNER_EMAIL']);

if ($newPassword) {
    $say('');
    $say('  NEW OWNER PASSWORD (shown once, only its hash is kept): '.$newPassword);
}
