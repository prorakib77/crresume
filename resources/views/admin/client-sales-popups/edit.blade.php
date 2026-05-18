<x-app-layout>
    <x-slot name="title">Edit Client Popup</x-slot>
    <x-slot name="pageTitle">Edit Client Popup</x-slot>
    <x-slot name="pageSubtitle">Update the popup message, targeting, and scheduling for client dashboard.</x-slot>

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

    <form method="POST" action="{{ route('admin.client-sales-popups.update', $popup) }}" enctype="multipart/form-data" class="space-y-6">
        @csrf
        @method('PUT')

        @include('admin.client-sales-popups._form', [
            'submitLabel' => 'Update Popup',
        ])
    </form>
</x-app-layout>
