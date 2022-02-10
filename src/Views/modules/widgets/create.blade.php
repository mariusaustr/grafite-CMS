@extends('cms::layouts.dashboard')

@section('pageTitle') Widgets @stop

@section('content')

    <div class="col-md-12 mt-2">
        @include('cms::modules.widgets.breadcrumbs', ['location' => ['create']])
    </div>

    <div class="col-md-12">
        {!! form()->open(['route' => cms()->route('widgets.store'), 'class' => 'add']) !!}

            {!! formMaker()->fromTable('widgets', config('cms.forms.widget')) !!}

            <div class="form-group text-right">
                <a href="{!! cms()->url('widgets') !!}" class="btn btn-secondary raw-left">Cancel</a>
                {!! form()->submit('Save', ['class' => 'btn btn-primary']) !!}
            </div>

        {!! form()->close() !!}
    </div>

@endsection
