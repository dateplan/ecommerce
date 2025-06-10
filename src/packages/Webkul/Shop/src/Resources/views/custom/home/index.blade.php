@extends('shop::custom.layouts.master')

@section('page_title')
    {{ __('shop::app.home.title') }}
@stop

@section('content-wrapper')
    <!-- ヒーローセクション -->
    <section class="hero bg-gradient-to-r from-blue-600 to-blue-800 text-white py-16 text-center">
        <div class="container mx-auto px-4">
            <h1 class="text-4xl font-bold mb-4">Welcome to {{ config('app.name') }}</h1>
            <p class="text-xl mb-8">Discover our amazing products and services</p>
            <a href="{{ route('shop.products.index') }}" class="bg-white text-blue-600 px-8 py-3 rounded-lg font-semibold hover:bg-gray-100 transition duration-300">
                Shop Now
            </a>
        </div>
    </section>

    <!-- おすすめ商品 -->
    <section class="py-12">
        <div class="container mx-auto px-4">
            <h2 class="text-3xl font-bold text-center mb-8">Featured Products</h2>
            
            <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-6">
                @foreach ($featuredProducts as $productFlat)
                    @include('shop::products.list.card', ['product' => $productFlat])
                @endforeach
            </div>
        </div>
    </section>

    <!-- 新着商品 -->
    <section class="py-12 bg-gray-50">
        <div class="container mx-auto px-4">
            <h2 class="text-3xl font-bold text-center mb-8">New Arrivals</h2>
            
            <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-6">
                @foreach ($newProducts as $productFlat)
                    @include('shop::products.list.card', ['product' => $productFlat])
                @endforeach
            </div>
        </div>
    </section>

    <!-- お知らせ -->
    <section class="py-12">
        <div class="container mx-auto px-4">
            <h2 class="text-3xl font-bold text-center mb-8">Latest News</h2>
            
            <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                @foreach ($posts as $post)
                    <div class="bg-white rounded-lg shadow-md overflow-hidden">
                        <img src="{{ $post->image_url }}" alt="{{ $post->title }}" class="w-full h-48 object-cover">
                        <div class="p-6">
                            <h3 class="text-xl font-semibold mb-2">{{ $post->title }}</h3>
                            <p class="text-gray-600 mb-4">{{ $post->excerpt }}</p>
                            <a href="#" class="text-blue-600 hover:underline">Read More</a>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </section>

    <!-- 特徴 -->
    <section class="py-12 bg-gray-50">
        <div class="container mx-auto px-4">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                <div class="text-center">
                    <div class="bg-blue-100 w-16 h-16 rounded-full flex items-center justify-center mx-auto mb-4">
                        <i class="fas fa-truck text-2xl text-blue-600"></i>
                    </div>
                    <h3 class="text-xl font-semibold mb-2">Free Shipping</h3>
                    <p class="text-gray-600">On orders over $50</p>
                </div>
                
                <div class="text-center">
                    <div class="bg-blue-100 w-16 h-16 rounded-full flex items-center justify-center mx-auto mb-4">
                        <i class="fas fa-undo text-2xl text-blue-600"></i>
                    </div>
                    <h3 class="text-xl font-semibold mb-2">Easy Returns</h3>
                    <p class="text-gray-600">30-day return policy</p>
                </div>
                
                <div class="text-center">
                    <div class="bg-blue-100 w-16 h-16 rounded-full flex items-center justify-center mx-auto mb-4">
                        <i class="fas fa-headset text-2xl text-blue-600"></i>
                    </div>
                    <h3 class="text-xl font-semibold mb-2">24/7 Support</h3>
                    <p class="text-gray-600">Dedicated support</p>
                </div>
            </div>
        </div>
    </section>
@stop
