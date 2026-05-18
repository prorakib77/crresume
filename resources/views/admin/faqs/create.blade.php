<x-app-layout>
    <x-slot name="title">Create FAQ</x-slot>
    <x-slot name="pageTitle">Create FAQ</x-slot>
    <x-slot name="pageSubtitle">Add a new frequently asked question for the website FAQ page.</x-slot>

    @if($errors->any())
        <div class="alert alert-danger mb-4">
            <div class="font-semibold">Please fix the validation errors below.</div>
            <ul class="mb-0 mt-2 list-disc ps-4">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form method="POST" action="{{ route('admin.faqs.store') }}" class="space-y-6">
        @csrf
        @include('admin.faqs._form', [
            'faq' => null,
            'submitLabel' => 'Create FAQ',
        ])
    </form>
</x-app-layout>
