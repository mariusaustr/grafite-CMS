@extends('cms::layouts.dashboard')

@section('pageTitle') Files @stop

@section('content')

    <div class="col-md-12 mt-2">
        @include('cms::modules.files.breadcrumbs', ['location' => ['edit']])
    </div>

    <div class="col-md-12 raw-margin-bottom-48 raw-margin-top-48 text-center">
        <a class="btn btn-secondary" href="{!! Cms::fileAsDownload($files->name, $files->location) !!}"><span class="fa fa-download"></span> Download: {!! $files->name !!}</a>
    </div>

    <div class="col-md-12">
        {!! form()->model($files, ['route' => [cms()->route('files.update'), $files->id], 'files' => true, 'method' => 'patch', 'class' => 'edit']) !!}

            {!! formMaker()->setColumns(2)->setSections([array_keys(config('cms.forms.file-edit'))])->fromObject($files, config('cms.forms.file-edit')) !!}

            <div class="form-group text-right">
                <a href="{!! cms()->url('files') !!}" class="btn btn-secondary raw-left">Cancel</a>
                {!! form()->field->submit('Save', ['class' => 'btn btn-primary']) !!}
            </div>

        {!! form()->close() !!}
    </div>

@endsection
