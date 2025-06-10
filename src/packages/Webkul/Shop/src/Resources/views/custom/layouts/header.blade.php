<header class="header">
    <div class="container mx-auto px-4">
        <div class="flex justify-between items-center">
            <!-- ロゴ -->
            <div class="logo">
                <a href="{{ route('shop.home.index') }}" class="text-2xl font-bold text-primary">
                    {{ config('app.name') }}
                </a>
            </div>
            
            <!-- メインナビゲーション -->
            <nav class="hidden md:block">
                <ul class="flex space-x-6">
                    <li><a href="{{ route('shop.home.index') }}" class="nav-link">ホーム</a></li>
                    <li><a href="{{ route('shop.home.about') }}" class="nav-link">会社概要</a></li>
                    <li><a href="{{ route('shop.products.index') }}" class="nav-link">商品一覧</a></li>
                    <li><a href="{{ route('shop.contact.index') }}" class="nav-link">お問い合わせ</a></li>
                </ul>
            </nav>
            
            <!-- アイコン -->
            <div class="flex items-center space-x-4">
                <a href="{{ route('customer.session.index') }}" class="text-gray-700 hover:text-primary">
                    <i class="icon account-icon"></i>
                </a>
                <a href="{{ route('shop.wishlist.index') }}" class="text-gray-700 hover:text-primary">
                    <i class="icon wishlist-icon"></i>
                </a>
                <a href="{{ route('shop.checkout.cart.index') }}" class="text-gray-700 hover:text-primary relative">
                    <i class="icon cart-icon"></i>
                    @if (Cart::getCart())
                        <span class="absolute -top-2 -right-2 bg-primary text-white text-xs rounded-full h-5 w-5 flex items-center justify-center">
                            {{ Cart::getCart()->items->count() }}
                        </span>
                    @endif
                </a>
            </div>
        </div>
    </div>
</header>
