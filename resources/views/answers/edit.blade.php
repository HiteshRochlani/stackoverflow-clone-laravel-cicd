@extends('layouts.app')

@push('styles')
    <link rel="stylesheet" type="text/css" href="https://unpkg.com/trix@2.0.0/dist/trix.css">
@endpush

@section('scripts')
    <script type="text/javascript" src="https://unpkg.com/trix@2.0.0/dist/trix.umd.min.js"></script>
@endsection
@section('content')
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header"><h1>Update the answer!</h1></div>
                    <div class="card-body">
                        <form action="{{ route('questions.answers.update', [$question->id, $answer->id]) }}" method="POST">
                            @csrf
                            @method('PUT')
                            <div class="form-group mt-3">
                                <label for="body" class="form-label">Body</label>
                                <input type="hidden" name="body" id="body" placeholder="Enter Answer" value="{{ old('body', $answer->body) }}" class="form-control @error('body') is-invalid @enderror" />
                                <trix-editor input="body"></trix-editor>
                                @error('body')
                                <span class="text-danger">{{ $message }}</span>
                                @enderror
                            </div>
                            <div class="form-group mt-3">
                                <input type="submit" name="submit" id="submit" class="btn btn-success"/>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
