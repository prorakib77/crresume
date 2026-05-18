<x-app-layout>
    <x-slot name="title">Edit Countdown Sale</x-slot>
    <x-slot name="pageTitle">Edit Countdown Sale</x-slot>
    <x-slot name="pageSubtitle">Update the campaign timer, visuals, and call-to-action.</x-slot>

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

    <form method="POST" action="{{ route('admin.sale-countdowns.update', $saleCountdown) }}" enctype="multipart/form-data" class="space-y-6">
        @csrf
        @method('PUT')

        @include('admin.sale-countdowns._form', [
            'submitLabel' => 'Update Countdown',
        ])
    </form>
</x-app-layout>
