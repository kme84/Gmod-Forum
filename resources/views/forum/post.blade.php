@extends('layout')
@section('title')
    Форум | {{$post->name}}
@endsection
@section('main_content')
<div class="container">
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="/forum">Форум</a></li>
            <li class="breadcrumb-item"><a href="/forum/{{$post->topic_id}}">{{$post->topic_name}}</a></li>
            <li class="breadcrumb-item active" aria-current="page">{{$post->name}}</li>
        </ol>
    </nav>
    <div class="row card mb-4">
        <div class="card-header">
            <div class="d-flex col-12 align-items-center">
                <img src={{$post->author_avatar ? asset('/storage/'.$post->author_avatar) : asset('/storage/static/noavatar.png')}} class="d-block ui-w-40 rounded-circle bg-light border border-secondary" width="96" height="96">
                <div class="ms-3">
                    <a class="text-decoration-none" href="javascript:void(0)" data-abc="true">{{$post->author_name}}</a>
                </div>
                {{-- <div class="text-muted small me-3 position-absolute end-0">
                    <div>Member since <strong>01/1/2019</strong></div>
                    <div><strong>134</strong> posts</div>
                </div> --}}
            </div>
        </div>
        <div class="card-body ck-content" id='content-' name='content-' author="{{$post->author_name}}">
            {!!$post->content!!}
        </div>
        <div class="card-footer d-flex flex-wrap justify-content-between align-items-center">
            <div class="text-muted small">📅 {{$post->created_at}}</div>
            @can('forum.'.$post->chapter_id.'.'.$post->topic_id.'.'.$post->id.'.create')
            <button type="button" class="btn btn-primary" onclick="comment(this.value);" value="">Ответить</button>
            @endcan
        </div>
    </div>
    @foreach ($post->comments as $key => $comment)
        @can('forum.'.$comment->chapter_id.'.'.$comment->topic_id.'.'.$comment->post_id.'.'.$comment->id.'.view')
        <div class="row card d-flex flex-row mb-4">
            <div class="bg-light d-flex flex-column align-items-center justify-content-center col-md-2">
                <img src={{$comment->author_avatar ? asset('/storage/'.$comment->author_avatar) : asset('/storage/static/noavatar.png')}} class="d-block ui-w-40 rounded-circle bg-light border border-secondary mt-2 mt-md-0" width="96" height="96">
                <a class="text-decoration-none" href="javascript:void(0)" data-abc="true">{{$comment->author_name}}</a>
            </div>
            <div class="card-body d-flex flex-column justify-content-between col-md-10">
                <div id='content-{{$key}}' name='content-{{$key}}' author="{{$comment->author_name}}" class="ck-content">
                    {!!$comment->content!!}
                </div>
                <hr>
                <div class="d-flex align-items-center justify-content-between flex-wrap">
                    <div class="text-muted small me-auto">📅 {{$comment->created_at}}</div>
                    @can('forum.'.$comment->chapter_id.'.'.$comment->topic_id.'.'.$comment->post_id.'.'.$comment->id.'.delete')
                    <form action='deletecomment' method='POST' name='deletecomment' id='deletecomment' enctype="multipart/form-data" onsubmit="return this.deletecomment.disabled=true;">
                        @csrf
                        <input type="hidden" name="id" id="id" value="{{$comment->id}}">
                        <input class="btn btn-danger ms-2" name="submit" type="submit" value="Удалить">
                    </form>
                    @endcan
                    @can('forum.'.$post->chapter_id.'.'.$post->topic_id.'.'.$post->id.'.create')
                    <button class="btn btn-primary ms-2" name="reply" id="reply" type="button" onclick="comment(this.value);" value="{{$key}}">Ответить</button>
                    @endcan
                </div>
            </div>
        </div>
        @endcan
    @endforeach
    @can('forum.'.$post->chapter_id.'.'.$post->topic_id.'.'.$post->id.'.create')
    <div class="row card d-flex flex-row mb-4">
        <div class="bg-light d-flex flex-column align-items-center justify-content-center col-md-2">
            <img src={{Auth::user()->avatar ? asset('/storage/'.Auth::user()->avatar) : asset('/storage/static/noavatar.png')}} class="d-block ui-w-40 rounded-circle bg-light border border-secondary mt-2 mt-md-0" width="96" height="96">
            <a class="text-decoration-none" href="javascript:void(0)" data-abc="true">{{Auth::user()->name}}</a>
        </div>
        <div class="card-body col-md-10">
            <form action='addcomment' method='POST' name='addcomment' id='addcomment' enctype="multipart/form-data" onsubmit="return this.addcomment.disabled=true;">
                @csrf
                <input type="hidden" name="id" id="id" value="{{$id}}">
                <textarea name="editor" id="editor"></textarea>
                <div class="d-flex flex-row-reverse"><input class="btn btn-primary mt-2" name="submit" type="submit" value="Отправить"></div>
            </form>
        </div>
    </div>
    @endcan
</div>
@push('scripts')
<script src="{{asset('ckeditor5-build-classic/ckeditor.js')}}"></script>
<script type="text/javascript">
    var myeditor;
    class MyUploadAdapter {
        constructor( loader ) {
            this.loader = loader;
        }
        upload() {
            return this.loader.file
                .then( file => new Promise( ( resolve, reject ) => {
                    this._initRequest();
                    this._initListeners( resolve, reject, file );
                    this._sendRequest( file );
                } ) );
        }
        abort() {
            if ( this.xhr ) {
                this.xhr.abort();
            }
        }
        _initRequest() {
            const xhr = this.xhr = new XMLHttpRequest();

            xhr.open( 'POST', '{{route('upload', ['_token' => csrf_token() ])}}', true );
            xhr.responseType = 'json';
        }

        _initListeners( resolve, reject, file ) {
            const xhr = this.xhr;
            const loader = this.loader;
            const genericErrorText = `Couldn't upload file: ${ file.name }.`;

            xhr.addEventListener( 'error', () => reject( genericErrorText ) );
            xhr.addEventListener( 'abort', () => reject() );
            xhr.addEventListener( 'load', () => {
                const response = xhr.response;

                if ( !response || response.error ) {
                    return reject( response && response.error ? response.error.message : genericErrorText );
                }

                resolve( {
                    default: response.url
                } );
            } );

            if ( xhr.upload ) {
                xhr.upload.addEventListener( 'progress', evt => {
                    if ( evt.lengthComputable ) {
                        loader.uploadTotal = evt.total;
                        loader.uploaded = evt.loaded;
                    }
                } );
            }
        }

        _sendRequest( file ) {
            const data = new FormData();
            data.append( 'upload', file );

            this.xhr.send( data );
        }
    }

    function MyCustomUploadAdapterPlugin( editor ) {
        editor.plugins.get( 'FileRepository' ).createUploadAdapter = ( loader ) => {
            return new MyUploadAdapter( loader );
        };
    }
    document.addEventListener('DOMContentLoaded', () =>
    {
    ClassicEditor
        .create( document.querySelector( '#editor' ), {
            extraPlugins: [ MyCustomUploadAdapterPlugin ],
        } )
        .then( editor => {
            myeditor = editor;
        } )
        .catch( error => {
            console.log( error );
        } );
    });
    function comment(id)
    {
        var elem = document.querySelector( '#content-' + id );
        const viewFragment = myeditor.data.processor.toView( "<blockquote>" + elem.attributes.author.value + ":\n" + elem.innerHTML + "</blockquote>");
        const modelFragment = myeditor.data.toModel( viewFragment );
        myeditor.model.insertContent( modelFragment );

        document.addcomment.scrollIntoView();
    }
</script>
@endpush
@endsection
