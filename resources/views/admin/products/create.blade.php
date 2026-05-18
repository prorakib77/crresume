<x-app-layout>
    <x-slot name="title">Create Product Card</x-slot>
    <x-slot name="pageTitle">Create Product Card</x-slot>
    <x-slot name="pageSubtitle">Add a new card for the welcome hero slider.</x-slot>

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

    <form method="POST" action="{{ route('admin.products.store') }}" enctype="multipart/form-data" class="space-y-6">
        @csrf
        @include('admin.products._form', [
            'product' => null,
            'submitLabel' => 'Create Product Card',
            'selectedType' => $selectedType,
            'typeOptions' => $typeOptions,
        ])
    </form>
</x-app-layout>
