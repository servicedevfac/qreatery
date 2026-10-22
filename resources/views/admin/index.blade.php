@include('admin.dashboard', [
    'adminUser' => $adminUser ?? auth()->user(),
])
