@extends('cms::layouts.dashboard')

@section('pageTitle') Pages @stop

@section('content')

    <div class="col-md-12 mt-2">
        @include('cms::modules.pages.breadcrumbs', ['location' => ['create']])
    </div>
    <div class="col-md-12 mt-4">
        {!! form()->open(['route' => cms()->route('pages.store'), 'class' => 'add', 'files' => true]) !!}

            {!! formMaker()->setColumns(2)->setSections([array_keys(config('cms.forms.page.identity'))])->fromTable('pages', config('cms.forms.page.identity')) !!}
            {!! formMaker()->setColumns(2)->setSections([array_keys(config('cms.forms.page.content'))])->fromTable('pages', config('cms.forms.page.content')) !!}
            {!! formMaker()->setColumns(2)->setSections([array_keys(config('cms.forms.page.seo'))])->fromTable('pages', config('cms.forms.page.seo')) !!}
            {!! formMaker()->setColumns(2)->setSections([array_keys(config('cms.forms.page.publish'))])->fromTable('pages', config('cms.forms.page.publish')) !!}

            <div class="form-group text-right">
                <a href="{!! cms()->url('pages') !!}" class="btn btn-secondary raw-left">Cancel</a>
                {!! form()->field->submit('Save', ['class' => 'btn btn-primary']) !!}
            </div>

        {!! form()->close() !!}
    </div>

@endsection
