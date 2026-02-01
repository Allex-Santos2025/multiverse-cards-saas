@extends('layouts.dashboard')

@section('content')
<livewire:store.dashboard.changelog-list :slug="$slug" />
@endsection