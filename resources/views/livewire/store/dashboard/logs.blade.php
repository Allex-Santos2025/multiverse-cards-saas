@extends('layouts.dashboard')

@section('content')
<livewire:store.dashboard.logs-list :slug="$slug" />
@endsection