@extends('template.base')

@section('content')
    <div class="max-w-8xl mx-auto">
        <div
            class="
                flex
                items-center
                justify-center
                w-full
                h-screen
                bg-gray-50
            "
            >
            <div class="px-40 py-20">
                <div class="flex flex-col items-center">
                    <h1 class="font-bold text-sushi-600 text-9xl">{{ $code }}</h1>

                    <h6 class="mb-2 text-2xl font-bold text-center text-gray-800 md:text-3xl">
                    {{ $message }}
                    </h6>
                </div>
            </div>
        </div>
    </div>
@endsection