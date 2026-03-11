{{-- resources/views/components/transaction-modal.blade.php --}}
<div id="modal-tx" class="hidden fixed inset-0 bg-black/50 backdrop-blur-sm z-50 flex items-center justify-center p-4">
    <div class="bg-white rounded-2xl p-8 w-full max-w-lg shadow-2xl">
        <h2 class="text-xl font-black mb-1">Registrar movimiento</h2>
        <p class="text-sm text-gray-500 mb-6">Agrega un gasto o ingreso a tu cuenta</p>

        <form method="POST" action="{{ route('transactions.store') }}">
            @csrf

            {{-- Type toggle --}}
            <div class="mb-5">
                <label class="block text-xs font-mono text-gray-400 uppercase tracking-widest mb-2">Tipo</label>
                <div class="grid grid-cols-2 gap-2">
                    <label class="cursor-pointer">
                        <input type="radio" name="type" value="expense" class="peer hidden" checked>
                        <div class="peer-checked:bg-red-50 peer-checked:border-red-500 peer-checked:text-red-600
                                    border-2 border-gray-200 rounded-lg p-3 text-center text-sm font-bold
                                    text-gray-500 transition-all cursor-pointer hover:border-gray-300">
                            📤 Gasto
                        </div>
                    </label>
                    <label class="cursor-pointer">
                        <input type="radio" name="type" value="income" class="peer hidden">
                        <div class="peer-checked:bg-green-50 peer-checked:border-green-500 peer-checked:text-green-700
                                    border-2 border-gray-200 rounded-lg p-3 text-center text-sm font-bold
                                    text-gray-500 transition-all cursor-pointer hover:border-gray-300">
                            📥 Ingreso
                        </div>
                    </label>
                </div>
            </div>

            {{-- Category (shown for expenses) --}}
            <div class="mb-5" id="cat-section">
                <label class="block text-xs font-mono text-gray-400 uppercase tracking-widest mb-2">
                    Categoría — Regla 50/30/20
                </label>
                <div class="grid grid-cols-3 gap-2">
                    @foreach([
                        ['value' => 'needs',   'label' => '🏠 Necesidades', 'sub' => '50%', 'checked' => true,
                         'bg' => 'bg-green-50', 'border' => 'border-green-600', 'text' => 'text-green-700'],
                        ['value' => 'wants',   'label' => '🎉 Deseos',       'sub' => '30%',
                         'bg' => 'bg-blue-50',  'border' => 'border-blue-600',  'text' => 'text-blue-700'],
                        ['value' => 'savings', 'label' => '💰 Ahorro',       'sub' => '20%',
                         'bg' => 'bg-amber-50', 'border' => 'border-amber-600', 'text' => 'text-amber-700'],
                    ] as $cat)
                    <label class="cursor-pointer">
                        <input type="radio" name="category" value="{{ $cat['value'] }}"
                               class="peer hidden" {{ isset($cat['checked']) ? 'checked' : '' }}>
                        <div class="peer-checked:{{ $cat['bg'] }} peer-checked:{{ $cat['border'] }} peer-checked:{{ $cat['text'] }}
                                    border-2 border-gray-200 rounded-lg p-3 text-center text-xs font-bold
                                    text-gray-500 transition-all cursor-pointer hover:border-gray-300">
                            {{ $cat['label'] }}<br>
                            <span class="font-normal opacity-70">{{ $cat['sub'] }}</span>
                        </div>
                    </label>
                    @endforeach
                </div>
            </div>

            {{-- Description --}}
            <div class="mb-4">
                <label class="block text-xs font-mono text-gray-400 uppercase tracking-widest mb-1.5">Descripción</label>
                <input type="text" name="description" required placeholder="ej. Renta, Spotify, Salario..."
                    class="w-full border border-gray-200 rounded-lg px-4 py-2.5 text-sm focus:outline-none focus:border-gray-900">
            </div>

            {{-- Amount + Date --}}
            <div class="grid grid-cols-2 gap-3 mb-4">
                <div>
                    <label class="block text-xs font-mono text-gray-400 uppercase tracking-widest mb-1.5">Monto</label>
                    <input type="number" name="amount" required min="0.01" step="0.01" placeholder="0.00"
                        class="w-full border border-gray-200 rounded-lg px-4 py-2.5 text-sm focus:outline-none focus:border-gray-900">
                </div>
                <div>
                    <label class="block text-xs font-mono text-gray-400 uppercase tracking-widest mb-1.5">Fecha</label>
                    <input type="date" name="date" value="{{ now()->toDateString() }}"
                        class="w-full border border-gray-200 rounded-lg px-4 py-2.5 text-sm focus:outline-none focus:border-gray-900">
                </div>
            </div>

            {{-- Notes --}}
            <div class="mb-6">
                <label class="block text-xs font-mono text-gray-400 uppercase tracking-widest mb-1.5">Nota (opcional)</label>
                <textarea name="notes" rows="2" placeholder="Detalle adicional..."
                    class="w-full border border-gray-200 rounded-lg px-4 py-2.5 text-sm focus:outline-none focus:border-gray-900 resize-none"></textarea>
            </div>

            <div class="flex justify-end gap-3 pt-4 border-t border-gray-100">
                <button type="button" onclick="document.getElementById('modal-tx').classList.add('hidden')"
                    class="px-5 py-2.5 border border-gray-200 rounded-lg text-sm font-semibold text-gray-600 hover:bg-gray-50">
                    Cancelar
                </button>
                <button type="submit"
                    class="px-5 py-2.5 bg-gray-900 text-white rounded-lg text-sm font-bold hover:bg-gray-700 transition-colors">
                    Guardar movimiento
                </button>
            </div>
        </form>
    </div>
</div>


{{-- resources/views/transactions/index.blade.php --}}
{{-- @extends('layouts.app') ... @section('content') --}}

{{-- resources/views/admin/clients/index.blade.php --}}
{{-- Admin client list view (trimmed for reference) --}}


{{-- resources/views/admin/clients/show.blade.php --}}
{{-- Admin single client detail view (trimmed for reference) --}}


{{-- resources/views/income/index.blade.php --}}
{{-- Income sources page (trimmed for reference) --}}
