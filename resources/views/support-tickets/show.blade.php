<x-app-layout>
    <x-slot name="title">{{ $supportTicket->display_reference }}</x-slot>
    <x-slot name="pageTitle">{{ $supportTicket->subject }}</x-slot>
    <x-slot name="pageSubtitle">
        {{ $supportTicket->display_reference }} / {{ $supportTicket->status_label }} / Updated {{ $supportTicket->last_message_at?->diffForHumans() ?? $supportTicket->created_at->diffForHumans() }}
    </x-slot>

    <livewire:support-tickets.thread :ticket="$supportTicket" />
</x-app-layout>
