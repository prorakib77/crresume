@php
    $user = auth()->user();
@endphp

<x-app-layout>
    <x-slot name="title">Support Tickets</x-slot>
    <x-slot name="pageTitle">Support Tickets</x-slot>
    <x-slot name="pageSubtitle">
        {{ $user->isAdmin()
            ? 'Create client tickets, assign agents, and manage every conversation from one workspace.'
            : ($user->isAgent()
                ? 'Track assigned conversations, respond quickly, and keep close requests moving.'
                : 'Open a ticket, follow replies, and keep every support conversation in one place.') }}
    </x-slot>

    <livewire:support-tickets.index />
</x-app-layout>
