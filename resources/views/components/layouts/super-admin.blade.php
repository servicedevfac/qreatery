@props([
    'title' => null,
    'headerTitle' => null,
    'active' => request('section', 'dashboard'),
    'adminUser' => auth()->user(),
])

@include('layouts.super-admin', [
    'title' => $title,
    'headerTitle' => $headerTitle,
    'active' => $active,
    'adminUser' => $adminUser,
    'slot' => $slot,
])
