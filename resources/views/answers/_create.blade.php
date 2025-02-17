@push('styles')
    <link rel="stylesheet" type="text/css" href="https://unpkg.com/trix@2.0.0/dist/trix.css">
@endpush
@section('scripts')
    <script type="text/javascript" src="https://unpkg.com/trix@2.0.0/dist/trix.umd.min.js"></script>
@endsection

<div class="row mt-4 justify-content-center">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header">
                <h3>Your Answer!</h3>
            </div>
            <div class="card-body">
                <form action="{{ route('questions.answers.store', $question->id) }}" method="POST">
                    @csrf
                    <div class="form-group mt-3">
                        <label for="body" class="form-label">Body</label>
                        <input type="hidden" name="body" id="body" placeholder="Enter Question" value="{{ old('body') }}" class="form-control @error('body') is-invalid @enderror" />
                        <trix-editor input="body"></trix-editor>
                        @error('body')
                            <span class="text-danger">{{ $message }}</span>
                        @enderror
                    </div>
                    <div class="form-group mt-2">
                        <button type="submit" class="btn btn-outline-success">Submit</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
