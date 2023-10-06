<!DOCTYPE html>
<html lang="en">
<head>
    <title>Assignment - Stepping Edge</title>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css">
    <script type="text/javascript" src="http://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.1/dist/umd/popper.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js"></script>
    <style>
        .hlw{
            color: red;
            font-weight: bold;
        }
    </style>
</head>
<body>
<div class="container">
    <div class="p-3 my-3 bg-dark text-white">
        <h1>Scan the document and print result</h1>
    </div>
    @if ($message = Session::get('success'))
        <div class="alert alert-success alert-block">
            <strong>{{ $message }}</strong>
        </div>
    @endif
    <form method="post" action="{{ route('doc.upload') }}" enctype="multipart/form-data">
        @csrf
        <div class="input-group mb-3">
            <input name="file" id="file" required accept=".pdf, .txt, .doc, .docx" type="file"
                   class="form-control-file">
            <br><br>
            <div class="input-group-append mt-3">
                <button type="submit" class="input-group-text">Upload Additional Evidence</button>
            </div>
            @error('file')
            <span class="text-danger">{{ $message }}</span>
            @enderror
        </div>
    </form>
    <br>
    <p>Search Keywords</p>
    <form method="get" action="{{ route('doc.search') }}">
        @php
            $searchText = isset($keyword) ? $keyword : '';
        @endphp
        <div class="input-group mb-3">
            <input type="text" name="keyword" required id="keyword" class="form-control" value="{{ $searchText }}"
                   placeholder="Search by keywords and click enter...">
            <div class="input-group-append">
                <button class="input-group-text">Search</button>
            </div>
        </div>
    </form>
    <br>
    @if(isset($text))
        <div class="container-fluid results">
            <p><b>Search List</b></p>
            <p>
                {!! $text !!}
            </p>
        </div>
    @endif
</div>
</body>
</html>
