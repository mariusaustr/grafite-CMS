@extends('cms::layouts.dashboard')

@section('pageTitle') Events @stop

@section('content')

    <div class="col-md-12 mt-2">
        @include('cms::modules.events.breadcrumbs', ['location' => ['create']])
    </div>

    <div class="col-md-12">
        {!! form()->open(['route' => cms()->route('events.store'), 'class' => 'add']) !!}

            {!! formMaker()->setColumns(3)->setSections([array_keys(config('cms.forms.event.identity'))])->fromTable('events', config('cms.forms.event.identity')) !!}
            {!! formMaker()->setColumns(1)->fromTable('events', config('cms.forms.event.content')) !!}
            {!! formMaker()->setColumns(2)->fromTable('events', config('cms.forms.event.seo')) !!}
            {!! formMaker()->setColumns(2)->setSections([array_keys(config('cms.forms.event.publish'))])->fromTable('events', config('cms.forms.event.publish')) !!}

            <div class="form-group text-right">
                <a href="{!! cms()->url('events') !!}" class="btn btn-secondary float-left">Cancel</a>
                {!! form()->field->submit('Save', ['class' => 'btn btn-primary']) !!}
            </div>

        {!! form()->close() !!}
    </div>

@endsection
