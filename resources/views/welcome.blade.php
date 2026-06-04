@extends('layouts.app')

@section('title', 'Вход в систему')

@section('content')
<div class="container container--auth">
    @auth
        <script>window.location.href = "{{ route('dashboard') }}";</script>
    @else
        <auth-container></auth-container>
    @endauth
</div>
@endsection