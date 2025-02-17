@extends('layouts.app')

@push('styles')
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.css" integrity="sha512-5A8nwdMOWrSz20fDsjczgUidUBR8liPYU+WymTZP1lmY9G6Oc7HlZv156XqnsgNUzTyMefFTcsFH/tnJE/+xBg==" crossorigin="anonymous" referrerpolicy="no-referrer" />
@endpush
@section('content')
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-12">
                @include('layouts._alert_messages')
            </div>
            
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header">
                        <h1>{{ $question->title }}</h1>
                    </div>
                    <div class="card-body">
                        {!! $question->body !!}
                    </div>
                    <div class="card-footer">
                        <div class="d-flex justify-content-between me-3">
                            <div class="d-flex">
                                <div>
                                    @auth
                                        <form action="{{ route('questions.vote', [$question->id, 1]) }}" method="POST">
                                            @csrf
                                            <button type="submit" title="Up Vote" class="up-vote d-block text-center {{ auth()->user()->hasQuestionUpVoted($question) ? 'text-success' : 'text-dark' }} btn"><i class="fa fa-caret-up fa-3x"></i></button>
                                        </form>
                                    @else
                                        <h5>Votes <br> Count</h5>
                                    @endauth
{{--                                    <a href="#" title="Up Vote" class="up-vote d-block text-center text-dark"><i class="fa fa-caret-up fa-3x"></i></a>--}}
                                    <h4 class="vote-count text-muted text-center m-0">{{ $question->votes_count }}</h4>
                                    @auth
                                        <form action="{{ route('questions.vote', [$question->id, -1]) }}" method="POST">
                                            @csrf
                                            <button type="submit" title="Down Vote" class="up-vote d-block text-center {{ auth()->user()->hasQuestionDownVoted($question) ? 'text-success' : 'text-dark' }} btn"><i class="fa fa-caret-down fa-3x"></i></button>
                                        </form>
                                    @endauth
{{--                                    <a href="#" title="Down Vote" class="down-vote d-block text-center text-dark"><i class="fa fa-caret-down fa-3x"></i></a>--}}
                                </div>
                                <div class="ms-4 mt-3 {{ $question->is_favorite ? 'text-warning' : 'text-dark' }}">
                                    @can('markAsFav', $question)
                                        <form action="{{ $question->is_favorite ?  route('questions.mark-as-unfav', $question->id) : route('questions.mark-as-fav', $question->id)}}" method="POST">
                                            @csrf
                                            @if($question->is_favorite)
                                                @method('DELETE')
                                            @endif
                                            <button type="submit" class="favorite btn d-block text-center mb-1 {{ $question->is_favorite ? 'text-warning' : 'text-dark' }}"><i class="fa fa-star fa-2x"></i></button>
                                        </form>
                                    @else
                                        <div><i class="fa fa-star fa-2x"></i></div>
                                    @endcan
                                    <h4 class="fav-count m-0 text-center">{{ $question->favorites_count }}</h4>
                                </div>
                            </div>
                            <div>
                                <div class="text-muted mb-2 text-end">
                                    Asked: {{ $question->created_date }}
                                </div>
                                <div class="d-flex mb-2">
                                    <div>
                                        <img src="{{ $question->owner->avatar }}">
                                    </div>
                                    <div class="mt-2">
                                        {{ $question->owner->name }}
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        @auth()
            @include('answers._create')
        @endauth
        @include('answers._index')
    </div>
@endsection
