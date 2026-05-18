@extends('layouts.app')

@section('title', 'Client Management')
@section('pageTitle', 'Client Management')
@section('pageSubtitle', 'Manage client accounts and assignments')

@section('content')
    <livewire:admin.clients-index />
@endsection
