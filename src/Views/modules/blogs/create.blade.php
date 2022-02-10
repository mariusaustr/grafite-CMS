@extends('cms::layouts.dashboard')

@section('pageTitle') Blog @stop

@section('content')

    <div class="col-md-12 mt-2">
        @include('cms::modules.blogs.breadcrumbs', ['location' => ['create']])
    </div>
    <div class="col-md-12">
        {!! form()->open(['route' => cms()->route('blog.store'), 'class' => 'add', 'files' => true]) !!}

            {!! formMaker()->setColumns(3)->fromTable('blogs', config('cms.forms.blog.identity')) !!}
            {!! formMaker()->setColumns(2)->fromTable('blogs', config('cms.forms.blog.content')) !!}
            {!! formMaker()->setColumns(2)->fromTable('blogs', config('cms.forms.blog.seo')) !!}
            {!! formMaker()->setColumns(2)->fromTable('blogs', config('cms.forms.blog.publish')) !!}

            <div class="form-group text-right">
                <a href="{!! cms()->url('blog') !!}" class="btn btn-secondary float-left">Cancel</a>
                {!! form()->submit('Save', ['class' => 'btn btn-primary']) !!}
            </div>

        {!! form()->close() !!}
    </div>

@endsection
