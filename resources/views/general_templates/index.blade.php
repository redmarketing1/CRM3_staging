@extends('layouts.main')
@section('page-title')
    {{ $notification_template->name }}
@endsection
@section('page-breadcrumb')
    {{ __('General Template') }}
@endsection
@push('css')
    <link rel="stylesheet" href="{{ asset('css/common.css') }}" type="text/css" />
@endpush

@push('scripts')
    <script src="{{ asset('Modules/Taskly/Resources/assets/js/tinymce/tinymce.min.js') }}"></script>
    <script>
        if ($(".pc-tinymce-2").length) {
            tinymce.init({
                selector: '.pc-tinymce-2',
                height: "400",
                content_style: 'body { font-family: "Inter", sans-serif; }'
            });
        }

        tinymce.init({
            selector: '.notification-content',
            height: 400,
            plugins: 'print preview importcss searchreplace autolink autosave save directionality visualblocks visualchars fullscreen image link media template codesample table charmap hr pagebreak nonbreaking anchor toc insertdatetime advlist lists wordcount imagetools textpattern noneditable help charmap quickbars emoticons',

            mobile: {
                plugins: 'print preview importcss searchreplace autolink autosave save directionality visualblocks visualchars fullscreen image link media template codesample table charmap hr pagebreak nonbreaking anchor toc insertdatetime advlist lists wordcount textpattern noneditable help charmap quickbars emoticons'
            },
            menu: {
                tc: {
                    title: 'Comments',
                    items: 'addcomment showcomments deleteallconversations'
                }
            },
            menubar: 'file edit view insert format tools table tc help',
            toolbar: 'undo redo | bold italic underline strikethrough | fontselect fontsizeselect formatselect | alignleft aligncenter alignright alignjustify | outdent indent |  numlist bullist checklist | forecolor backcolor casechange formatpainter removeformat | pagebreak | charmap emoticons | fullscreen  preview save print | insertfile image media template link anchor codesample | a11ycheck ltr rtl | showcomments addcomment',
            autosave_ask_before_unload: true,
            autosave_interval: '30s',
            autosave_prefix: '{path}{query}-{id}-',
            autosave_restore_when_empty: false,
            autosave_retention: '2m',
            image_advtab: true,
            image_class_list: [{
                    title: 'None',
                    value: ''
                },
                {
                    title: 'Some class',
                    value: 'class-name'
                }
            ],
            importcss_append: true,
            templates: [{
                    title: 'New Table',
                    description: 'creates a new table',
                    content: '<div class="mceTmpl"><table width="98%%"  border="0" cellspacing="0" cellpadding="0"><tr><th scope="col"> </th><th scope="col"> </th></tr><tr><td> </td><td> </td></tr></table></div>'
                },
                {
                    title: 'Starting my story',
                    description: 'A cure for writers block',
                    content: 'Once upon a time...'
                },
                {
                    title: 'New list with dates',
                    description: 'New List with dates',
                    content: '<div class="mceTmpl"><span class="cdate">cdate</span><br /><span class="mdate">mdate</span><h2>My List</h2><ul><li></li><li></li></ul></div>'
                }
            ],
            template_cdate_format: '[Date Created (CDATE): %m/%d/%Y : %H:%M:%S]',
            template_mdate_format: '[Date Modified (MDATE): %m/%d/%Y : %H:%M:%S]',
            image_caption: true,
            quickbars_selection_toolbar: 'bold italic | quicklink h2 h3 blockquote quickimage quicktable',
            noneditable_noneditable_class: 'mceNonEditable',
            toolbar_mode: 'sliding',
            spellchecker_ignore_list: ['Ephox', 'Moxiecode'],
            tinycomments_mode: 'embedded',
            content_style: '.mymention{ color: gray; }',
            contextmenu: 'link image imagetools table configurepermanentpen',
            a11y_advanced_options: true,
            /*
            The following settings require more configuration than shown here.
            For information on configuring the mentions plugin, see:
            https://www.tiny.cloud/docs/plugins/premium/mentions/.
            */
        });
    </script>
@endpush

@section('page-action')
    <div class="row">
        <div class="text-end mb-3">
            <div class="text-end">
                <div class="d-flex justify-content-end drp-languages">


                    <div class="d-flex justify-content-end">
                        <ul class="list-unstyled py-1">
                            <li class="dropdown dash-h-item drp-language">
                                <a class="dash-head-link dropdown-toggle arrow-none me-0" href="#" data-size="lg"
                                    class="btn btn-sm btn-primary" data-ajax-popup="true" data-size="lg"
                                    data-title="{{ __('Add New Message Template') }}"
                                    data-url="{{ route('general-templates.create') }}" data-toggle="tooltip"
                                    title="{{ __('Add New Message Template') }}">
                                    <span class="drp-text hide-mob text-primary">
                                        Add New
                                    </span>
                                </a>
                            </li>
                        </ul>
                    </div>

                    @if (isset($plansettings['enable_chatgpt']) && $plansettings['enable_chatgpt'] == 'on')
                        <ul class="list-unstyled mb-0 m-2 py-2">
                            <li class="dropdown dash-h-item drp-language">
                                <div class="text-end">
                                    <a href="#" data-size="md" class="btn btn-sm btn-primary"
                                        data-ajax-popup-over="true" data-size="md"
                                        data-title="{{ __('Generate product Name') }}"
                                        data-url="{{ route('generate', ['notification template']) }}" data-toggle="tooltip"
                                        title="{{ __('Generate') }}">
                                        <i class="fas fa-robot"> {{ __('Generate With AI') }}</i>
                                    </a>
                                </div>
                            </li>
                        </ul>
                    @endif
                    <ul class="list-unstyled mb-0 m-2">
                        <li class="dropdown dash-h-item drp-language">
                            <a class="dash-head-link dropdown-toggle arrow-none me-0" data-bs-toggle="dropdown"
                                href="#" role="button" aria-haspopup="false" aria-expanded="false"
                                id="dropdownLanguage">
                                <span
                                    class="drp-text hide-mob text-primary">{{ Str::upper($curr_noti_tempLang->lang) }}</span>
                                <i class="ti ti-chevron-down drp-arrow nocolor"></i>
                            </a>
                            <div class="dropdown-menu dash-h-dropdown dropdown-menu-end" aria-labelledby="dropdownLanguage">
                                @foreach ($languages as $k => $lang)
                                    <a href="{{ route('general-templates.index', [$notification_template->id, $k]) }}"
                                        class="dropdown-item {{ $curr_noti_tempLang->lang == $k ? 'text-primary' : '' }}">{{ Str::upper($k) }}</a>
                                @endforeach
                            </div>
                        </li>
                    </ul>
                    <ul class="list-unstyled mb-0 m-2">
                        <li class="dropdown dash-h-item drp-language">
                            <a class="dash-head-link dropdown-toggle arrow-none me-0" data-bs-toggle="dropdown"
                                href="#" role="button" aria-haspopup="false" aria-expanded="false"
                                id="dropdownLanguage">
                                <span
                                    class="drp-text hide-mob text-primary">{{ __('Template: ') }}{{ $notification_template->name }}</span>
                                <i class="ti ti-chevron-down drp-arrow nocolor"></i>
                            </a>
                            <div class="dropdown-menu dash-h-dropdown dropdown-menu-end" aria-labelledby="dropdownLanguage">
                                @foreach ($notification_templates as $notification_template)
                                    <a href="{{ route('general-templates.index', [$notification_template->id, Request::segment(3) ? Request::segment(3) : \Auth::user()->lang]) }}"
                                        class="dropdown-item {{ $notification_template->name == $notification_template->name ? 'text-primary' : '' }}">{{ $notification_template->name }}
                                    </a>
                                @endforeach
                            </div>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
@endsection

@php

@endphp


@section('content')
    <div class="row">
        <div class="col-xl-12">
            <div class="card">
                <div class="card-body ">
                    <h5 class= "font-weight-bold pb-3">{{ __('Placeholders') }}</h5>
                    <div class="col-lg-12 col-md-12 col-sm-12">
                        <div class="card">
                            <div class="card-header card-body">
                                <div class="row text-xs">
                                    <h6 class="font-weight-bold mb-4">{{ __('Variables') }}</h6>
                                    @php
                                        $variables = json_decode($curr_noti_tempLang->variables);
                                    @endphp

                                    @if (!empty($variables) > 0)
                                        @foreach ($variables as $key => $var)
                                            <div class="col-6 pb-1">
                                                <p class="mb-1">{{ __($key) }} : <span
                                                        class="pull-right text-primary">{{ '{' . $var . '}' }}</span></p>
                                            </div>
                                        @endforeach
                                    @endif
                                </div>

                            </div>
                        </div>
                    </div>
                    {{ Form::model($curr_noti_tempLang, ['route' => ['general-templates.update', $curr_noti_tempLang->parent_id], 'method' => 'PUT']) }}
                    <div class="row">
                        <div class="form-group col-12">
                            {{ Form::label('content', __('Notification Message'), ['class' => 'form-label text-dark']) }}
                            {{ Form::textarea('content', $curr_noti_tempLang->content, ['class' => 'form-control notification-content', 'required' => 'required', 'rows' => '04', 'placeholder' => 'EX. Hello, {company_name}']) }}
                            <small>{{ __('A variable is to be used in such a way.') }} <span
                                    class="text-primary">{{ __('Ex. Hello, {company_name}') }}</span></small>
                        </div>
                    </div>
                    <hr>
                    <div class="col-md-12 text-end">
                        {{ Form::hidden('lang', null) }}
                        <input type="submit" value="{{ __('Save Changes') }}"
                            class="btn btn-print-invoice  btn-primary m-r-10">
                    </div>
                    {{ Form::close() }}
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        $(document).ready(function() {
            $(document).ajaxComplete(function() {
                tinymce.remove();
                document.querySelectorAll('.tinyMCE').forEach(function(editor) {
                    init_tiny_mce('#' + editor.id);
                });
            });
        });
    </script>
@endpush
