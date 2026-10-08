@include('admin.layouts.partials._header')

<body  class="app-blank bgi-size-cover bgi-position-center bgi-no-repeat">
    <!--begin::Root-->
    <div class="d-flex flex-column flex-root" >
        <!--begin::Authentication - Error 404-->
        <div class="d-flex flex-column flex-center flex-column-fluid">
            <!--begin::Content-->
            <div class="d-flex flex-column flex-center text-center p-10">
                <!--begin::Wrapper-->
                <div class="card card-flush w-lg-650px py-5 shadow-sm border-0 border">
                    <div class="card-body py-15 py-lg-20">
                        <!--begin::Title-->
                        <h1 class="fw-bold fs-1 text-body mb-4">
                            Oops! Page Not Found
                        </h1>
                        <!--end::Title-->
                        
                        <!--begin::Text-->
                        <div class="fw-semibold text-muted mb-7">
                            We can't find the page you're looking for. <br/>
                            It might have been removed, renamed, or did not exist in the first place.
                        </div>
                        <!--end::Text-->

                        <!--begin::Illustration-->
                        <div class="mb-10">
                            <i class="ti ti-map-pin x text-primary opacity-50 mb-4 d-block"></i>
                            <div class="x fw-bold text-gray-200">404</div>
                        </div>
                        <!--end::Illustration-->

                        <!--begin::Link-->
                        <div class="mb-0">
                            <a href="{{ url('/') }}" class="btn btn-primary fw-bold px-6 py-3">
                                <i class="ti ti-home h4 me-2"></i> Return Home
                            </a>
                        </div>
                        <!--end::Link-->
                    </div>
                </div>
                <!--end::Wrapper-->
            </div>
            <!--end::Content-->
        </div>
        <!--end::Authentication - Error 404-->
    </div>
    <!--end::Root-->

    @include('admin.layouts.partials._scripts')
</body>
</html>
