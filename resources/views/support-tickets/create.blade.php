@php
    $user = auth()->user();
@endphp

<x-app-layout>
    <x-slot name="title">{{ $user->isAdmin() ? 'Create Support Ticket' : 'New Support Ticket' }}</x-slot>
    <x-slot name="pageTitle">{{ $user->isAdmin() ? 'Create Support Ticket' : 'New Support Ticket' }}</x-slot>
    <x-slot name="pageSubtitle">
        {{ $user->isAdmin()
            ? 'Open a ticket for a client and optionally assign the conversation to an agent.'
            : 'Share what you need help with and keep the reply thread in one place.' }}
    </x-slot>

    <livewire:support-tickets.index :open-composer="$openComposer ?? true" />
</x-app-layout>
