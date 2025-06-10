<footer class="footer">
    <div class="container mx-auto px-4">
        <div class="grid grid-cols-1 md:grid-cols-4 gap-8">
            <!-- 会社情報 -->
            <div class="col-span-1">
                <h3 class="text-xl font-semibold mb-4">会社情報</h3>
                <p class="text-gray-300">
                    株式会社サンプル<br>
                    〒123-4567<br>
                    東京都渋谷区渋谷1-1-1
                </p>
            </div>
            
            <!-- カスタマーサポート -->
            <div class="col-span-1">
                <h3 class="text-xl font-semibold mb-4">カスタマーサポート</h3>
                <ul class="space-y-2">
                    <li><a href="#" class="text-gray-300 hover:text-white">お問い合わせ</a></li>
                    <li><a href="#" class="text-gray-300 hover:text-white">よくある質問</a></li>
                    <li><a href="#" class="text-gray-300 hover:text-white">配送・送料について</a></li>
                    <li><a href="#" class="text-gray-300 hover:text-white">返品・交換について</a></li>
                </ul>
            </div>
            
            <!-- アカウント -->
            <div class="col-span-1">
                <h3 class="text-xl font-semibold mb-4">アカウント</h3>
                <ul class="space-y-2">
                    @auth('customer')
                        <li><a href="{{ route('customer.profile.index') }}" class="text-gray-300 hover:text-white">マイアカウント</a></li>
                        <li><a href="{{ route('customer.orders.index') }}" class="text-gray-300 hover:text-white">注文履歴</a></li>
                        <li><a href="{{ route('customer.wishlist.index') }}" class="text-gray-300 hover:text-white">お気に入り</a></li>
                    @else
                        <li><a href="{{ route('customer.session.index') }}" class="text-gray-300 hover:text-white">ログイン</a></li>
                        <li><a href="{{ route('customer.register.index') }}" class="text-gray-300 hover:text-white">会員登録</a></li>
                    @endauth
                </ul>
            </div>
            
            <!-- SNSリンク -->
            <div class="col-span-1">
                <h3 class="text-xl font-semibold mb-4">SNSでフォローする</h3>
                <div class="flex space-x-4">
                    <a href="#" class="text-gray-300 hover:text-white"><i class="fab fa-facebook-f"></i></a>
                    <a href="#" class="text-gray-300 hover:text-white"><i class="fab fa-twitter"></i></a>
                    <a href="#" class="text-gray-300 hover:text-white"><i class="fab fa-instagram"></i></a>
                    <a href="#" class="text-gray-300 hover:text-white"><i class="fab fa-line"></i></a>
                </div>
                
                <!-- ニュースレター登録 -->
                <div class="mt-6">
                    <h4 class="text-lg font-medium mb-2">ニュースレター購読</h4>
                    <form action="#" method="POST" class="flex">
                        <input type="email" placeholder="メールアドレス" class="px-4 py-2 rounded-l w-full" required>
                        <button type="submit" class="bg-primary text-white px-4 py-2 rounded-r">登録</button>
                    </form>
                </div>
            </div>
        </div>
        
        <!-- コピーライト -->
        <div class="border-t border-gray-700 mt-8 pt-8 text-center text-gray-400">
            <p>&copy; {{ date('Y') }} {{ config('app.name') }}. All Rights Reserved.</p>
        </div>
    </div>
</footer>
