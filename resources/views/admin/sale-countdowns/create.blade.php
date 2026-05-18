<x-app-layout>
    <x-slot name="title">Create Countdown Sale</x-slot>
    <x-slot name="pageTitle">Create Countdown Sale</x-slot>
    <x-slot name="pageSubtitle">Publish a timed campaign section on the welcome page.</x-slot>

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

    <form method="POST" action="{{ route('admin.sale-countdowns.store') }}" enctype="multipart/form-data" class="space-y-6">
        @csrf
        @include('admin.sale-countdowns._form', [
            'saleCountdown' => null,
            'submitLabel' => 'Create Countdown',
        ])
    </form>
</x-app-layout>
