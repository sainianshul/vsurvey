@include('admin.layouts.partials._header')
<body>
    <script>
        var mode = localStorage.getItem('data-bs-theme') || 'light';
        document.body.setAttribute('data-bs-theme', mode);
    </script>
    <div class="page">
        <!-- Sidebar -->
        @include('admin.layouts.partials._sidebar')
        
        <!-- Navbar -->
        @include('admin.layouts.partials._navbar')
        
        <div class="page-wrapper">
            <!-- Page body -->
            <div class="page-body pt-1 mt-1">
                <div class="container-xl">
                    @yield('content')
                </div>
            </div>
            
            <!-- Footer -->
            @include('admin.layouts.partials._footer')
        </div>
    </div>
    
    @include('admin.layouts.partials._scripts')
</body>
</html>