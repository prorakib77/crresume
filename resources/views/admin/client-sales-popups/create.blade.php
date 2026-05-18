<x-app-layout>
    <x-slot name="title">Create Client Popup</x-slot>
    <x-slot name="pageTitle">Create Client Popup</x-slot>
    <x-slot name="pageSubtitle">Add a dashboard sales popup for recurring clients or one specific client.</x-slot>

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

    <form method="POST" action="{{ route('admin.client-sales-popups.store') }}" enctype="multipart/form-data" class="space-y-6">
        @csrf
        @include('admin.client-sales-popups._form', [
            'popup' => null,
            'submitLabel' => 'Create Popup',
        ])
    </form>
</x-app-layout>
