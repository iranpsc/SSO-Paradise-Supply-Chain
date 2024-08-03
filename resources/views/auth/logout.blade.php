@extends('layouts.app')

@section('content')
    <div >
        <div class="flex flex-col gap-7">
            

            
                <form action="{{ route('logout') }}" method="post">
                   <div class="flex flex-col gap-7 w-full xl:w-1/2 2xl:w-[40%] mx-auto">
                    @csrf

                    
                    <div class="flex items-center justify-center">
                        <button type="submit" class="text-white bg-primery-blue dark:bg-dark-yellow py-[14px] px-[40px] mx-auto rounded-xl w-full md:w-max">{{ __('Logout') }}</button>
                    </div>
                   </div>
                 
                </form>
          
        </div>
    </div>
@endsection