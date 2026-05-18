<x-app-layout>
    <x-slot name="title">Edit Product Card</x-slot>
    <x-slot name="pageTitle">Edit Product Card</x-slot>
    <x-slot name="pageSubtitle">Update pricing, image, and Buy Now link for this card.</x-slot>

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

    <form method="POST" action="{{ route('admin.products.update', $product) }}" enctype="multipart/form-data" class="space-y-6">
        @csrf
        @method('PUT')

        @include('admin.products._form', [
            'submitLabel' => 'Update Product Card',
            'selectedType' => $selectedType,
            'typeOptions' => $typeOptions,
        ])
    </form>
</x-app-layout>
