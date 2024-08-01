@extends('layouts.app')

@section('content')
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="text-center">


                    <div class="text-xs md:text-xl font-normal dark:text-[#FFFFFF]">
                        @if (session('status'))
                            <div class="alert alert-success" role="alert">
                                {{ session('status') }}
                            </div>
                        @endif

                        {{ __('You are logged in!') }}

                        @if (in_array(Auth::user()->code, ['hm-2000001', 'hm-2000005']))
                            <form action="{{ route('users.import') }}" method="post" enctype="multipart/form-data">
                                @csrf
                                <input type="file" name="file" class="border-2 border-gray-300 p-2 w-full">
                                <button type="submit"
                                    class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">Import
                                    Users</button>
                            </form>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
