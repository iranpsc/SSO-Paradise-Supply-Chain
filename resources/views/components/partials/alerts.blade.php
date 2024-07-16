
@session('success')
    <div class="text-green-600 m-5">
        {{ session('success') }}
    </div>
@endsession

@session('error')
    <div class="text-red-600 m-5">
        {{ session('error') }}
    </div>
@endsession

@session('warning')
    <div class="text-yellow-500 m-5">
        {{ session('warning') }}
    </div>
@endsession

@session('info')
    <div class="text-primery-blue m-5">
        {{ session('info') }}
    </div>
@endsession

@session('status')
    <div class="text-primery-blue m-5">
        {{ session('status') }}
    </div>
@endsession
