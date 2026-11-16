<?php

namespace App\Http\Requests;

use App\Enums\EntryType;
use App\Models\Category;
use App\Models\Entry;
use Closure;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Checks an upload, for both "new" and "edit". The rules follow the type:
 * a book needs a PDF, a video an MP4 or WebM, audio an audio file, and an
 * article needs its text instead of a file.
 */
class EntryRequest extends FormRequest
{
    /** Asked before the rules run, so someone with no right to the entry gets "forbidden", not a list of form errors. */
    public function authorize(): bool
    {
        $entry = $this->route('entry');

        return $entry instanceof Entry
            ? $this->user()->can('update', $entry)
            : $this->user()->can('create', Entry::class);
    }

    /** The type being edited (it can't change), or the one picked on the upload form. */
    public function entryType(): ?EntryType
    {
        $entry = $this->route('entry');

        return $entry instanceof Entry ? $entry->type : EntryType::tryFrom((string) $this->input('type'));
    }

    public function rules(): array
    {
        $type = $this->entryType();
        $creating = ! $this->route('entry') instanceof Entry;

        $rules = [
            'type' => [Rule::requiredIf($creating), Rule::enum(EntryType::class)],
            'title' => ['required', 'string', 'min:2', 'max:120'],
            'creator' => ['required', 'string', 'max:80'],
            'category_id' => ['required', 'integer', function (string $attribute, mixed $value, Closure $fail) {
                if (! Category::query()->whereKey($value)->exists()) {
                    $fail('Choose a category from the list.');
                }
            }],
            'year' => ['nullable', 'integer', 'between:-3000,'.date('Y')],
            'description' => ['required', 'string', 'min:10', 'max:1000'],
            'body' => [$type === EntryType::Article ? 'required' : 'nullable', 'string', 'max:30000'],
            'link' => ['nullable', 'url:https', 'max:255'],
            'duration' => ['nullable', 'integer', 'between:1,86400'],
            'cover' => ['nullable', 'file', 'mimes:jpg,jpeg,png,webp', 'max:'.config('shop.max_kb.cover'), 'dimensions:max_width=5000,max_height=5000'],
        ];

        if ($type?->hasFile()) {
            // Editing keeps the file you already uploaded unless you choose a new one.
            $rules['file'] = [$creating ? 'required' : 'nullable', 'file', 'mimes:'.implode(',', array_keys($type->fileTypes())), 'max:'.$type->maxKb()];
        }

        return $rules;
    }

    public function messages(): array
    {
        $type = $this->entryType();
        $megabytes = fn (int $kb) => rtrim(rtrim(number_format($kb / 1024, 1), '0'), '.').' MB';

        return [
            'file.required' => $type ? 'Choose the '.strtolower($type->label()).' file to upload.' : 'Choose a file to upload.',
            'file.mimes' => $type ? 'That file type isn\'t accepted here. Use: '.implode(', ', array_keys($type->fileTypes())).'.' : 'That file type isn\'t accepted.',
            'file.max' => $type ? 'That file is too big. The limit is '.$megabytes($type->maxKb()).'.' : 'That file is too big.',
            'file.uploaded' => 'The file didn\'t arrive. It may be bigger than this server allows.',
            'cover.mimes' => 'The cover must be a JPG, PNG or WebP picture.',
            'cover.max' => 'The cover is too big. The limit is '.$megabytes((int) config('shop.max_kb.cover')).'.',
            'cover.uploaded' => 'The cover didn\'t arrive. It may be bigger than this server allows.',
            'body.required' => 'Write the article before publishing it.',
            'link.url' => 'The link must start with https://',
        ];
    }

    public function attributes(): array
    {
        return [
            'creator' => strtolower($this->entryType()?->creatorLabel() ?? 'creator'),
            'category_id' => 'category',
        ];
    }
}
