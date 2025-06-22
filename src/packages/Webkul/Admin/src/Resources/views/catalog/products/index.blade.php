<x-admin::layouts>
    <x-slot:title>@lang('admin::app.catalog.products.index.title')</x-slot>

    <div class="flex items-center justify-between gap-4 max-sm:flex-wrap">
        <p class="text-xl font-bold text-gray-800 dark:text-white">
            @lang('admin::app.catalog.products.index.title')
        </p>

        <div class="flex items-center gap-x-2.5">
            @if (bouncer()->hasPermission('catalog.products.create'))
                <a href="{{ route('admin.catalog.products.store') }}" class="primary-button">
                    @lang('admin::app.catalog.products.index.create-btn')
                </a>
            @endif
        </div>
    </div>

    <div class="mt-4 bg-white rounded-lg shadow-sm">
        <div class="overflow-x-auto">
            <table class="w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="text-center px-3 py-3 text-xs font-medium text-gray-500 uppercase tracking-wider">ID</th>
                        <th class="text-center px-3 py-3 text-xs font-medium text-gray-500 uppercase tracking-wider">商品名</th>
                        <th class="text-center px-3 py-3 text-xs font-medium text-gray-500 uppercase tracking-wider">SKU</th>
                        <th class="text-center px-3 py-3 text-xs font-medium text-gray-500 uppercase tracking-wider">価格</th>
                        <th class="text-center px-3 py-3 text-xs font-medium text-gray-500 uppercase tracking-wider">在庫</th>
                        <th class="text-center px-3 py-3 text-xs font-medium text-gray-500 uppercase tracking-wider">ステータス
                        </th>
                        <th class="text-center px-3 py-3 text-xs font-medium text-gray-500 uppercase tracking-wider">タイプ</th>
                        <th class="text-center px-3 py-3 text-xs font-medium text-gray-500 uppercase tracking-wider">操作</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse($products as $product)
                        @php
                            $name = $product->attribute_values->firstWhere('attribute_code', 'name')?->text_value ?? 'No Name';
                            $price = $product->attribute_values->firstWhere('attribute_code', 'price')?->float_value ?? 0;
                            $status = $product->attribute_values->firstWhere('attribute_code', 'status')?->boolean_value ?? false;
                        @endphp
                        <tr class="hover:bg-gray-50">
                            <td class="text-center px-3 py-4 text-sm text-gray-900">{{ $product->id }}</td>
                            <td class="text-center px-3 py-4 text-sm">
                                <div class="flex justify-center">
                                    <span class="font-medium text-gray-900">
                                        {{ $name }}
                                        @if($product->type === 'configurable')
                                            <span class="ml-1 text-xs text-gray-500">(設定可能)</span>
                                        @endif
                                    </span>
                                </div>
                            </td>
                            <td class="text-center px-3 py-4 text-sm text-gray-500">{{ $product->sku ?? 'N/A' }}</td>
                            <td class="text-center px-3 py-4 text-sm text-gray-500">
                                @if($product->type === 'configurable')
                                    N/A
                                @else
                                    {{ core()->formatPrice($price) }}
                                @endif
                            </td>
                            <td class="text-center px-3 py-4 text-sm text-gray-500">{{ $product->inventories->sum('qty') ?? 0 }}</td>
                            <td class="text-center px-3 py-4 text-sm">
                                @if($status)
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">有効</span>
                                @else
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">無効</span>
                                @endif
                            </td>
                            <td class="text-center px-3 py-4 text-sm text-gray-500">{{ $product->type }}</td>
                            <td class="text-center px-3 py-4 text-sm">
                                <div class="flex justify-center space-x-3">
                                    <a href="{{ route('admin.catalog.products.edit', $product->id) }}" class="text-indigo-600 hover:text-indigo-900">
                                        <span class="icon-edit"></span>
                                    </a>
                                    <form action="{{ route('admin.catalog.products.delete', $product->id) }}" method="POST" onsubmit="return confirm('本当に削除しますか？')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="text-red-600 hover:text-red-900">
                                            <span class="icon-trash"></span>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="px-6 py-4 text-center text-sm text-gray-500">商品が見つかりません</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    @if($products->hasPages())
        <div class="mt-4">
            {{ $products->links() }}
        </div>
    @endif

    {!! view_render_event('bagisto.admin.catalog.products.list.after') !!}
</x-admin::layouts>
