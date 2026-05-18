<x-app-layout>
    <x-slot name="title">Notices</x-slot>
    <x-slot name="pageTitle">Notices</x-slot>
    <x-slot name="pageSubtitle">Important payment, onboarding, and account notices.</x-slot>

    @include('notices.partials.list', [
        'notices' => $notices,
        'noticeCount' => $noticeCount,
        'pageHeading' => 'Client Notices',
        'pageCopy' => 'Payment requests, onboarding reminders, and important notices appear here.',
        'emptyCopy' => 'There are no active notices for your account right now.',
    ])
</x-app-layout>
