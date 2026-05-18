<x-app-layout>
    <x-slot name="title">Edit Review Card</x-slot>
    <x-slot name="pageTitle">Edit Review Card</x-slot>
    <x-slot name="pageSubtitle">Update testimonial details, review image, and visibility.</x-slot>

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

    <form method="POST" action="{{ route('admin.reviews.update', $review) }}" enctype="multipart/form-data" class="space-y-6">
        @csrf
        @method('PUT')

        @include('admin.reviews._form', [
            'submitLabel' => 'Update Review Card',
        ])
    </form>
</x-app-layout>
