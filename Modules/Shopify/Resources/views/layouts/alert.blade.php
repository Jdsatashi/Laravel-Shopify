@if(session('success'))
    @if(is_array(session('success')))
        @foreach(session('success') as $successMessage)
            <div class="alert alert-success alert-dismissible fade show">
                {{ $successMessage }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endforeach
    @else
        <div class="alert alert-success alert-dismissible fade show">
            {{ $successMessage }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif
@endif

@if(session('warning'))
    @if(is_array(session('warning')))
        @foreach(session('warning') as $warningMessage)
            <div class="alert alert-warning alert-dismissible fade show">{{ $warningMessage }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endforeach
    @else
        <div class="alert alert-warning alert-dismissible fade show">{{ session('warning') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif
@endif