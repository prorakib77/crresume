<x-app-layout>
    <x-slot name="title">Notices</x-slot>
    <x-slot name="pageTitle">Notices</x-slot>
    <x-slot name="pageSubtitle">Important notices and team updates.</x-slot>

    @include('notices.partials.list', [
        'notices' => $notices,
        'noticeCount' => $noticeCount,
        'pageHeading' => 'Agent Notices',
        'pageCopy' => 'Review important admin notices, workflow updates, and reminders for your account.',
        'emptyCopy' => 'There are no active notices for your account right now.',
    ])
</x-app-layout>
