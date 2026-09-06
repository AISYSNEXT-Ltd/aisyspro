<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StorePublicInquiryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'type' => ['required', Rule::in(['contact', 'quote'])],
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255'],
            'phone' => ['nullable', 'required_if:type,quote', 'string', 'max:40'],
            'company' => ['nullable', 'string', 'max:255'],
            'subject' => ['nullable', 'required_if:type,contact', 'string', 'max:255'],
            'requested_solution' => ['nullable', 'string', 'max:255'],
            'message' => ['nullable', 'required_if:type,contact', 'string', 'min:10', 'max:5000'],
            'activity' => ['nullable', 'required_if:type,quote', 'string', 'max:255'],
            'company_size' => ['nullable', Rule::in(['solo', '2-10', '11-50', '51-200', '200-plus'])],
            'user_count' => ['nullable', 'integer', 'min:1', 'max:10000'],
            'current_tools' => ['nullable', 'array', 'max:10'],
            'current_tools.*' => ['string', 'distinct', 'max:120'],
            'hosting_preference' => ['nullable', Rule::in(['included', 'existing', 'undecided'])],
            'desired_timeline' => ['nullable', Rule::in(['urgent', '1-3-months', '3-6-months', 'flexible'])],
            'budget_range' => ['nullable', Rule::in(['under-1000', '1000-3000', '3000-10000', 'over-10000', 'undecided'])],
            'pack_slug' => [
                'nullable',
                'required_if:type,quote',
                Rule::exists('packs', 'slug')->where(fn ($query) => $query->where('status', 'published')),
            ],
            'option_slugs' => ['nullable', 'array', 'max:20'],
            'option_slugs.*' => [
                'string',
                'distinct',
                Rule::exists('offer_options', 'slug')->where(fn ($query) => $query->where('status', 'published')),
            ],
            'consent' => ['required', 'accepted'],
            'submission_uuid' => ['required', 'uuid'],
            'source_url' => ['nullable', 'url', 'max:2048'],
            'website' => ['nullable', 'max:0'],
        ];
    }
}
