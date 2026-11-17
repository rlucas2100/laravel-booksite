@php
    use App\Enums\EntryType;

    $editing = $entry !== null;
    $chosen = $editing ? $type : (EntryType::tryFrom((string) old('type')) ?? $type);

    // What the Alpine component needs to know about each type.
    $types = [];
    foreach (EntryType::cases() as $case) {
        $types[$case->value] = [
            'label' => $case->label(),
            'creator' => $case->creatorLabel(),
            'hasFile' => $case->hasFile(),
            'extensions' => array_keys($case->fileTypes()),
            'accept' => $case->accept(),
            'limit' => $case->maxKb(),
        ];
    }

    // The server's own ceiling for one upload, in case it is lower than ours.
    $serverLimit = \Illuminate\Http\UploadedFile::getMaxFilesize();
@endphp
<x-layout :title="$editing ? 'Edit: '.$entry->title : 'Upload'">
    <div class="shell max-w-4xl py-10" x-data="entryForm(@js(['type' => $chosen->value, 'types' => $types, 'coverLimit' => (int) config('shop.max_kb.cover'), 'duration' => old('duration')]))">
        <p class="eyebrow text-soft">{{ $editing ? 'Editing' : 'New on the shelf' }}</p>
        <h1 class="mt-1 text-4xl font-black sm:text-5xl">{{ $editing ? $entry->title : 'Upload something' }}</h1>
        @unless ($editing)
            <p class="mt-2 max-w-2xl text-lg text-soft">A book, a video, a recording or a blog post. Only share what you have the right to share.</p>
        @endunless

        @if (! $editing && \App\Support\Sandbox::enabled() && ! \App\Support\Sandbox::unrestricted())
            <p class="card mt-6 flex items-start gap-3 bg-sun p-4 text-night">
                <x-icon name="info" class="mt-0.5"/>
                <span>This is the public demo, so your upload will only be visible in <b>this browser</b> and is removed after {{ config('shop.sandbox_hours') }} hours. You can add up to {{ config('shop.sandbox_max_uploads') }}.</span>
            </p>
        @endif

        @if ($errors->any())
            <div class="card mt-6 border-tomato bg-card p-4" role="alert">
                <p class="font-bold">That didn’t go through. Please check:</p>
                <ul class="mt-1 list-disc pl-5 text-sm">
                    @foreach ($errors->all() as $message)
                        <li>{{ $message }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form method="POST" action="{{ $editing ? route('entries.update', $entry) : route('entries.store') }}" enctype="multipart/form-data" class="mt-8 space-y-8">
            @csrf
            @if ($editing)
                @method('PUT')
            @endif

            {{-- 1. What is it? (fixed once it has been uploaded) --}}
            @if ($editing)
                <p class="tone-{{ $type->colour() }}"><span class="chip chip-tone text-sm"><x-icon :name="$type->icon()" size="h-4 w-4"/> {{ $type->label() }}</span></p>
            @else
                <fieldset>
                    <legend class="label text-base">What are you adding?</legend>
                    <div class="mt-1 grid grid-cols-2 gap-3 sm:grid-cols-4">
                        @foreach (EntryType::cases() as $case)
                            <label class="card tone-{{ $case->colour() }} flex cursor-pointer flex-col items-start gap-2 p-4 transition-transform hover:-translate-y-0.5"
                                   :class="type === '{{ $case->value }}' ? 'bg-tone border-night' : 'opacity-80'">
                                <input type="radio" name="type" value="{{ $case->value }}" class="sr-only" @checked($chosen === $case) @change="switchTo('{{ $case->value }}')">
                                <span class="grid h-10 w-10 place-items-center rounded-xl border-2 border-night bg-cream text-night"><x-icon :name="$case->icon()"/></span>
                                <span class="font-display text-xl font-black">{{ $case->label() }}</span>
                                <span class="text-xs font-semibold">{{ $case->hasFile() ? strtoupper(implode(', ', array_keys($case->fileTypes()))) : 'Written here' }}</span>
                            </label>
                        @endforeach
                    </div>
                    <x-error for="type"/>
                </fieldset>
            @endif

            {{-- 2. The details --}}
            <div class="card grid gap-5 p-5 sm:grid-cols-2 sm:p-7">
                <div class="sm:col-span-2">
                    <label class="label" for="title">Title</label>
                    <input id="title" class="input" type="text" name="title" value="{{ old('title', $entry?->title) }}" maxlength="120" required autofocus>
                    <x-error for="title"/>
                </div>

                <div>
                    <label class="label" for="creator" x-text="kind.creator">{{ $chosen->creatorLabel() }}</label>
                    <input id="creator" class="input" type="text" name="creator" value="{{ old('creator', $entry?->creator) }}" maxlength="80" required>
                    <x-error for="creator"/>
                </div>

                <div>
                    <label class="label" for="category_id">Category</label>
                    <select id="category_id" class="input" name="category_id" required>
                        <option value="" disabled @selected(! old('category_id', $entry?->category_id))>Choose one…</option>
                        @foreach ($categories as $category)
                            <option value="{{ $category->id }}" @selected((int) old('category_id', $entry?->category_id) === $category->id)>{{ $category->name }}</option>
                        @endforeach
                    </select>
                    <x-error for="category_id"/>
                </div>

                <div class="sm:col-span-2" x-data="counted(1000)">
                    <label class="label" for="description">Short description</label>
                    <textarea id="description" class="input" name="description" rows="3" maxlength="1000" required x-ref="text" @input="count"
                              placeholder="Two or three sentences to make someone want it.">{{ old('description', $entry?->description) }}</textarea>
                    <p class="hint text-right tabular-nums"><span x-text="used">0</span> / 1000</p>
                    <x-error for="description"/>
                </div>

                <div x-show="type !== 'article'">
                    <label class="label" for="year">Year <span class="font-normal text-soft">(optional)</span></label>
                    <input id="year" class="input" type="number" name="year" value="{{ old('year', $entry?->year) }}" max="{{ date('Y') }}" placeholder="First published or made">
                    <x-error for="year"/>
                </div>

                <div>
                    <label class="label" for="price">Price in £ <span class="font-normal text-soft">(optional)</span></label>
                    <input id="price" class="input" type="number" name="price" min="0" max="99.99" step="0.01" inputmode="decimal"
                           value="{{ old('price', $entry?->price ? number_format($entry->price / 100, 2, '.', '') : '') }}" placeholder="Leave empty to give it away">
                    <p class="hint">Members then pay before they can download or play it.</p>
                    <x-error for="price"/>
                </div>

                <div :class="type === 'article' ? '' : 'sm:col-span-2'">
                    <label class="label" for="link">Link <span class="font-normal text-soft">(optional)</span></label>
                    <input id="link" class="input" type="url" name="link" value="{{ old('link', $entry?->link) }}" maxlength="255" placeholder="https://… where to find out more">
                    <x-error for="link"/>
                </div>

                {{-- An article's text, or a book's opening lines --}}
                <div class="sm:col-span-2" x-show="type === 'article' || type === 'book'">
                    <label class="label" for="body" x-text="type === 'article' ? 'The article' : 'Opening lines (optional)'">{{ $chosen === EntryType::Article ? 'The article' : 'Opening lines (optional)' }}</label>
                    <textarea id="body" class="input font-mono text-sm" name="body" :rows="type === 'article' ? 16 : 4" :required="type === 'article'" maxlength="30000"
                              :placeholder="type === 'article' ? 'Write in Markdown: ## Heading, **bold**, - lists, [links](https://…)' : 'The first sentence or two, to give readers a taste.'">{{ old('body', $entry?->body) }}</textarea>
                    <p class="hint" x-show="type === 'article'">Markdown is supported. HTML is removed.</p>
                    <x-error for="body"/>
                </div>
            </div>

            {{-- 3. The files --}}
            <div class="grid gap-5 md:grid-cols-2">
                <div x-show="kind.hasFile" class="tone-{{ $chosen->colour() }}">
                    <p class="label" id="file-label">
                        <span x-text="'The ' + kind.label.toLowerCase() + ' file'">The file</span>
                        @if ($editing && $entry->file_path)
                            <span class="font-normal text-soft">(leave empty to keep the current one)</span>
                        @endif
                    </p>
                    <label class="dropzone" :class="{ 'is-over': over === 'file' }"
                           @dragover.prevent="over = 'file'" @dragleave.prevent="over = null" @drop.prevent="dropFile($event)">
                        <input type="file" name="file" class="sr-only" x-ref="file" :accept="kind.accept" :disabled="! kind.hasFile"
                               @if (! $editing) :required="kind.hasFile" @endif @change="chooseFile($event)" aria-labelledby="file-label">
                        <x-icon name="file" size="h-9 w-9"/>
                        <template x-if="! file">
                            <span>
                                <span class="block font-bold">Drop it here, or click to choose</span>
                                <span class="block text-sm text-soft" x-text="kind.extensions.join(', ').toUpperCase() + ', up to ' + megabytes(kind.limit * 1024)"></span>
                            </span>
                        </template>
                        <template x-if="file">
                            <span>
                                <span class="block font-bold break-all" x-text="file.name"></span>
                                <span class="block text-sm text-soft" x-text="megabytes(file.size) + (duration ? ' · ' + Math.floor(duration / 60) + ' min ' + (duration % 60) + ' s' : '')"></span>
                            </span>
                        </template>
                    </label>
                    <input type="hidden" name="duration" :value="duration">
                    <p class="field-error" x-show="fileError" x-text="fileError" x-cloak role="alert"></p>
                    <x-error for="file"/>
                    @if ($editing && $entry->file_path)
                        <p class="hint">Current file: {{ strtoupper($entry->fileExtension()) }}@if ($entry->fileSizeLabel()), {{ $entry->fileSizeLabel() }}@endif</p>
                    @endif
                </div>

                <div :class="kind.hasFile ? '' : 'md:col-span-2'">
                    <p class="label" id="cover-label">Cover picture <span class="font-normal text-soft">(optional)</span></p>
                    <label class="dropzone" :class="{ 'is-over': over === 'cover' }"
                           @dragover.prevent="over = 'cover'" @dragleave.prevent="over = null" @drop.prevent="dropCover($event)">
                        <input type="file" name="cover" class="sr-only" x-ref="cover" accept=".jpg,.jpeg,.png,.webp" @change="chooseCover($event)" aria-labelledby="cover-label">
                        <template x-if="coverPreview">
                            <img :src="coverPreview" alt="" class="max-h-36 rounded-md border-2 border-line">
                        </template>
                        <template x-if="! coverPreview">
                            <x-icon name="image" size="h-9 w-9"/>
                        </template>
                        <span>
                            <span class="block font-bold" x-text="cover ? cover.name : 'Drop a picture, or click to choose'"></span>
                            <span class="block text-sm text-soft">JPG, PNG or WebP, up to {{ number_format(config('shop.max_kb.cover') / 1024, 0) }} MB. No picture? We’ll draw a cover for you.</span>
                        </span>
                    </label>
                    <p class="field-error" x-show="coverError" x-text="coverError" x-cloak role="alert"></p>
                    <x-error for="cover"/>
                    <p class="hint" x-show="cover" x-cloak><button type="button" class="cursor-pointer font-bold underline" @click="clearCover">Remove this picture</button></p>

                    @if ($editing && $entry->cover_path)
                        <label class="mt-3 flex items-center gap-2 text-sm font-semibold">
                            <input type="checkbox" name="remove_cover" value="1" class="h-4 w-4 accent-tomato">
                            Remove the current cover and use a drawn one
                        </label>
                    @endif
                </div>
            </div>

            <p class="hint">This server accepts files up to {{ \Illuminate\Support\Number::fileSize($serverLimit) }} each.</p>

            <div class="flex flex-wrap items-center gap-3 border-t-2 border-line/15 pt-6">
                <button type="submit" class="btn btn-lg btn-primary">
                    <x-icon :name="$editing ? 'check' : 'upload'"/> {{ $editing ? 'Save changes' : 'Put it on the shelf' }}
                </button>
                <a href="{{ $editing ? $entry->url() : route('entries.mine') }}" class="btn btn-lg btn-plain">Cancel</a>
            </div>
        </form>
    </div>
</x-layout>
