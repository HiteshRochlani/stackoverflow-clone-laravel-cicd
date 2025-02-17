@if (session('success'))
    <div class="alert alert-success text-center fs-5">
        {{ session('success') }}
    </div>
@endif

@if (session('error'))
    <div class="alert alert-danger text-center fs-5">
        {{ session('error') }}
    </div>
@endif
