<x-app-layout>
    <x-slot name="title">Edit FAQ</x-slot>
    <x-slot name="pageTitle">Edit FAQ</x-slot>
    <x-slot name="pageSubtitle">Update website FAQ content, order, and visibility.</x-slot>

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

    <form method="POST" action="{{ route('admin.faqs.update', $faq) }}" class="space-y-6">
        @csrf
        @method('PUT')

        @include('admin.faqs._form', [
            'submitLabel' => 'Update FAQ',
        ])
    </form>
</x-app-layout>
