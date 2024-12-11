
@session('success')
    <div class="bg-black/10 backdrop-blur-md flex justify-center items-center z-[20000] h-screen w-screen fixed right-0 top-[-40px]">
        <div class="flex items-center justify-center bg-white dark:bg-[#0F0F0E] rounded-xl flex-col p-5">
            <div class="text-green-600 m-5 text-center">
                {{ session('success') }}
            </div>
        </div>
    </div>
@endsession

@session('error')
    <div class="text-red-600 m-5 text-center">
        {{ session('error') }}
    </div>
@endsession

@session('warning')
    <div class="bg-black/10 blur-md flex justify-center items-center z-20">
        <div class="flex items-center justify-center bg-white dark:bg-[#0F0F0E] rounded-xl flex-col p-5">
            <div class="text-yellow-500 m-5 text-center">
                {{ session('warning') }}
            </div>
        </div>
    </div>
@endsession

@session('info')
    <div class="text-primery-blue m-5 text-center">
        {{ session('info') }}
    </div>
@endsession

@session('status')
    <div class="text-primery-blue m-5 text-center">
        {{ session('status') }}
    </div>
@endsession
