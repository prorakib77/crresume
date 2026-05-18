@extends('layouts.app')

@section('title', 'Agent Management')
@section('pageTitle', 'Agent Management')
@section('pageSubtitle', 'Track agent activities and performance')

@section('content')
    <livewire:admin.agents-index />
@endsection
