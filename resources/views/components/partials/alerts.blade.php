
@session('success')
    <div class="alert alert-success">
        {{ session('success') }}
    </div>
@endsession

@session('error')
    <div class="alert alert-danger">
        {{ session('error') }}
    </div>
@endsession

@session('warning')
    <div class="alert alert-warning">
        {{ session('warning') }}
    </div>
@endsession

@session('info')
    <div class="alert alert-info">
        {{ session('info') }}
    </div>
@endsession

@session('status')
    <div class="alert alert-info">
        {{ session('status') }}
    </div>
@endsession
