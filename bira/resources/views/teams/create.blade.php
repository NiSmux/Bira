@extends('layouts.app')

@section('title', 'Create new team')

@section('content')
<div class="max-w-2xl mx-auto px-8 py-12">
    <div class="mb-8">
        <a href="{{ route('teams.index') }}" class="inline-flex items-center gap-2 text-sm text-muted-foreground hover:text-white transition-colors mb-4">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
            Back to list
        </a>
        <h2 class="text-3xl font-bold tracking-tight text-white">New team</h2>
        <p class="text-muted-foreground mt-1 text-sm">Create a space for collaboration with your colleagues.</p>
    </div>

    <div class="bg-card border border-border-subtle rounded-2xl p-8 shadow-sm">
        @include('teams.partials.create_form')
    </div>
</div>
@endsection