<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Banco Mundial – Panel Administrativo</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.2/Sortable.min.js"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        'bm-navy': '#003366',
                        'bm-blue': '#0052A4',
                        'bm-gold': '#C9A84C',
                        'bm-light': '#E8F0FB',
                    }
                }
            }
        }
    </script>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap');
        body { font-family: 'Inter', sans-serif; }
        .sortable-ghost { opacity: 0.3; background: #C9A84C; border-radius: 1rem; }
        .drag-handle { cursor: grab; }
        .drag-handle:active { cursor: grabbing; }
    </style>
</head>
<body class="bg-bm-light min-h-screen">

    <!-- Header -->
    <header class="bg-bm-navy border-b-4 border-bm-gold shadow-xl">
        <div class="max-w-6xl mx-auto px-6 py-4 flex items-center justify-between">
            <div class="flex items-center gap-4">
                <div class="w-12 h-12 rounded-full bg-bm-gold flex items-center justify-center">
                    <span class="text-bm-navy font-black text-lg">BM</span>
                </div>
                <div>
                    <h1 class="text-white font-bold text-xl">Banco Mundial</h1>
                    <p class="text-bm-gold text-xs uppercase tracking-widest">Panel Administrativo</p>
                </div>
            </div>
            <div class="flex gap-3">
                <a href="{{ route('turnos.index') }}" class="text-xs bg-blue-700 hover:bg-blue-600 text-white px-4 py-2 rounded-lg transition">🏠 Inicio</a>
                <a href="{{ route('turnos.pantalla') }}" target="_blank" class="text-xs bg-bm-gold hover:bg-yellow-600 text-bm-navy font-semibold px-4 py-2 rounded-lg transition">📺 Pantalla</a>
            </div>
        </div>
    </header>

    <main class="max-w-6xl mx-auto px-6 py-8">

        <!-- Alertas -->
        @if(session('success'))
            <div class="mb-6 bg-green-50 border border-green-300 text-green-700 rounded-xl px-5 py-4 text-sm flex items-center gap-2">
                ✅ {{ session('success') }}
            </div>
        @endif
        @if(session('info'))
            <div class="mb-6 bg-blue-50 border border-bm-blue text-bm-blue rounded-xl px-5 py-4 text-sm flex items-center gap-2">
                ℹ️ {{ session('info') }}
            </div>
        @endif

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">

            <!-- Panel izquierdo: turno actual + acciones -->
            <div class="space-y-6">

                <!-- Turno actual -->
                <div class="bg-white rounded-2xl shadow-lg overflow-hidden border-t-4 border-bm-gold">
                    <div class="px-6 py-4 bg-bm-navy">
                        <p class="text-bm-gold font-bold text-xs uppercase tracking-widest">Turno en Atención</p>
                    </div>
                    <div class="px-6 py-6 text-center">
                        @if($turnoActual)
                            <p class="text-6xl font-black text-bm-navy font-mono">{{ $turnoActual['codigo'] }}</p>
                            <p class="text-gray-600 mt-1">{{ $turnoActual['nombre'] }}</p>
                            <span class="mt-2 inline-block bg-bm-light text-bm-navy text-xs font-semibold px-3 py-1 rounded-full">{{ $turnoActual['tipo'] }}</span>
                        @else
                            <p class="text-gray-400 text-lg py-4">Sin turno activo</p>
                        @endif
                    </div>
                </div>

                <!-- Acción: Siguiente turno -->
                <div class="bg-white rounded-2xl shadow-lg p-6">
                    <h3 class="text-bm-navy font-bold text-sm uppercase tracking-widest mb-4">Gestión de Cola</h3>
                    <form action="{{ route('turnos.siguiente') }}" method="POST">
                        @csrf
                        <button type="submit"
                            class="w-full bg-bm-navy hover:bg-bm-blue text-white font-bold py-4 rounded-xl transition flex items-center justify-center gap-2 text-sm uppercase tracking-wider shadow
                            {{ count($cola) === 0 ? 'opacity-50 cursor-not-allowed' : '' }}"
                            {{ count($cola) === 0 ? 'disabled' : '' }}>
                            ▶ Llamar Siguiente Turno
                        </button>
                    </form>
                    <p class="text-gray-400 text-xs text-center mt-3">Marca el primer turno como atendido y llama al siguiente</p>
                </div>

                <!-- Estadísticas -->
                <div class="bg-white rounded-2xl shadow-lg p-6">
                    <h3 class="text-bm-navy font-bold text-sm uppercase tracking-widest mb-4">Resumen del Día</h3>
                    @php
                        $resumen = ['CA' => 0, 'SC' => 0, 'CR' => 0];
                        foreach($cola as $t) $resumen[$t['prefijo']] = ($resumen[$t['prefijo']] ?? 0) + 1;
                    @endphp
                    <div class="space-y-3">
                        @foreach(['CA' => ['Caja', '💰'], 'SC' => ['Servicio', '🛎'], 'CR' => ['Créditos', '💳']] as $pref => [$label, $icon])
                        <div class="flex items-center justify-between">
                            <div class="flex items-center gap-2">
                                <span>{{ $icon }}</span>
                                <span class="text-gray-600 text-sm">{{ $label }}</span>
                                <span class="text-xs text-gray-400 font-mono">({{ $pref }})</span>
                            </div>
                            <span class="font-black text-bm-navy text-lg">{{ $resumen[$pref] }}</span>
                        </div>
                        @endforeach
                        <div class="border-t pt-3 flex justify-between">
                            <span class="text-gray-700 font-semibold text-sm">Total en espera</span>
                            <span class="font-black text-bm-navy text-lg">{{ count($cola) }}</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Panel derecho: lista de turnos reordenable -->
            <div class="lg:col-span-2">
                <div class="bg-white rounded-2xl shadow-lg overflow-hidden">
                    <div class="bg-gradient-to-r from-bm-navy to-bm-blue px-6 py-5 flex items-center justify-between">
                        <div>
                            <h2 class="text-white font-bold text-lg">Cola de Turnos</h2>
                            <p class="text-blue-300 text-xs mt-0.5">Arrastre para reordenar · Usa los botones para cancelar</p>
                        </div>
                        <span class="bg-bm-gold text-bm-navy font-black text-sm px-4 py-1.5 rounded-full">
                            {{ count($cola) }}
                        </span>
                    </div>

                    @if(count($cola) === 0)
                        <div class="text-center py-20 text-gray-400">
                            <p class="text-5xl mb-4">📋</p>
                            <p class="text-lg font-medium text-gray-500">No hay turnos en cola</p>
                            <p class="text-sm mt-1">Los nuevos turnos aparecerán aquí</p>
                        </div>
                    @else
                        <ul id="sortable-cola" class="divide-y divide-gray-100 p-4 space-y-2">
                            @foreach($cola as $i => $turno)
                            <li class="flex items-center gap-4 bg-gray-50 hover:bg-bm-light rounded-xl px-4 py-3 transition group border {{ $i === 0 ? 'border-bm-gold' : 'border-transparent' }}"
                                data-id="{{ $turno['id'] }}">

                                <!-- Handle -->
                                <span class="drag-handle text-gray-300 group-hover:text-bm-gold text-lg select-none" title="Arrastrar">⠿</span>

                                <!-- Posición -->
                                <span class="text-gray-400 font-bold text-sm w-6 text-center">{{ $i + 1 }}</span>

                                <!-- Código -->
                                <div class="w-20 text-center">
                                    <span class="font-mono font-black text-bm-navy text-lg">{{ $turno['codigo'] }}</span>
                                </div>

                                <!-- Info -->
                                <div class="flex-1">
                                    <p class="font-semibold text-gray-800 text-sm">{{ $turno['nombre'] }}</p>
                                    <p class="text-gray-400 text-xs mt-0.5">{{ $turno['tipo'] }} · {{ $turno['hora'] }}</p>
                                </div>

                                <!-- Badge primer lugar -->
                                @if($i === 0)
                                    <span class="bg-bm-gold text-bm-navy text-xs font-bold px-2 py-0.5 rounded-full">Próximo</span>
                                @endif

                                <!-- Cancelar -->
                                <form action="{{ route('turnos.cancel', $turno['id']) }}" method="POST"
                                      onsubmit="return confirm('¿Cancelar el turno {{ $turno['codigo'] }} de {{ $turno['nombre'] }}?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit"
                                        class="text-gray-300 hover:text-red-500 transition text-lg font-bold px-2 py-1 rounded-lg hover:bg-red-50"
                                        title="Cancelar turno">✕</button>
                                </form>
                            </li>
                            @endforeach
                        </ul>
                    @endif
                </div>

                <!-- Instrucciones -->
                <div class="mt-4 bg-blue-50 rounded-xl border border-bm-blue border-opacity-30 px-5 py-4 text-xs text-bm-navy">
                    <strong>💡 Instrucciones:</strong>
                    Arrastre las filas para reordenar la cola. El cambio se guarda automáticamente.
                    Use el botón <strong>✕</strong> para cancelar un turno.
                    El botón <strong>▶ Llamar Siguiente</strong> atiende el primer turno de la lista.
                </div>
            </div>
        </div>
    </main>

    <script>
        const token = "{{ csrf_token() }}";

        const el = document.getElementById('sortable-cola');
        if (el) {
            Sortable.create(el, {
                animation: 200,
                handle: '.drag-handle',
                ghostClass: 'sortable-ghost',
                onEnd: async function() {
                    const ids = [...el.querySelectorAll('li')].map(li => li.dataset.id);
                    try {
                        const res = await fetch("{{ route('turnos.reordenar') }}", {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': token,
                                'Accept': 'application/json',
                            },
                            body: JSON.stringify({ orden: ids }),
                        });
                        const data = await res.json();
                        if (!data.ok) alert('Error al reordenar.');
                    } catch(e) {
                        alert('Error de conexión al reordenar.');
                    }
                }
            });
        }
    </script>
</body>
</html> 