<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Banco Mundial – Solicitar Turno</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        'bm-navy':  '#003366',
                        'bm-blue':  '#0052A4',
                        'bm-gold':  '#C9A84C',
                        'bm-light': '#E8F0FB',
                    }
                }
            }
        }
    </script>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap');
        body { font-family: 'Inter', sans-serif; }
    </style>
</head>
<body class="bg-bm-light min-h-screen">

    <!-- Header -->
    <header class="bg-bm-navy shadow-lg">
        <div class="max-w-5xl mx-auto px-6 py-4 flex items-center justify-between">
            <div class="flex items-center gap-4">
                <div class="w-12 h-12 rounded-full bg-bm-gold flex items-center justify-center">
                    <span class="text-bm-navy font-bold text-lg">BM</span>
                </div>
                <div>
                    <h1 class="text-white font-bold text-xl tracking-wide">Banco Mundial</h1>
                    <p class="text-blue-300 text-xs">Sistema de Atención al Cliente</p>
                </div>
            </div>
            <nav class="flex gap-3">
                <a href="{{ route('turnos.pantalla') }}" class="text-xs bg-bm-blue hover:bg-blue-700 text-white px-4 py-2 rounded-lg transition">📺 Pantalla</a>
                <a href="{{ route('turnos.admin') }}" class="text-xs bg-bm-gold hover:bg-yellow-600 text-bm-navy font-semibold px-4 py-2 rounded-lg transition">⚙ Admin</a>
            </nav>
        </div>
    </header>

    <main class="max-w-2xl mx-auto px-6 py-10">

        <!-- Turno generado -->
        @if(session('turno_nuevo') || $turnoNuevo)
            @php $tn = session('turno_nuevo') ?? $turnoNuevo; @endphp
            <div class="mb-8 bg-white border-l-4 border-bm-gold rounded-2xl shadow-xl p-6 text-center animate-pulse-once">
                <p class="text-bm-navy text-sm font-medium uppercase tracking-widest mb-1">Tu número de turno</p>
                <p class="text-7xl font-black text-bm-navy tracking-wider">{{ $tn['codigo'] }}</p>
                <p class="mt-2 text-gray-500 text-sm">{{ $tn['nombre'] }} · {{ $tn['tipo'] }} · {{ $tn['hora'] }}</p>
                @php
                    $pos = collect($cola)->search(fn($t) => $t['id'] === $tn['id']);
                    $delante = $pos !== false ? $pos : count($cola);
                @endphp
                <div class="mt-4 inline-block bg-bm-light text-bm-navy font-semibold px-5 py-2 rounded-full text-sm border border-bm-blue">
                    @if($delante == 0)
                        🎉 ¡Es tu turno! Dirígete a la ventanilla.
                    @else
                        👥 Tienes <strong>{{ $delante }}</strong> {{ $delante == 1 ? 'persona' : 'personas' }} por delante
                    @endif
                </div>
            </div>
        @endif

        <!-- Formulario -->
        <div class="bg-white rounded-2xl shadow-lg overflow-hidden">
            <div class="bg-gradient-to-r from-bm-navy to-bm-blue px-8 py-6">
                <h2 class="text-white text-2xl font-bold">Solicitar Turno</h2>
                <p class="text-blue-200 text-sm mt-1">Complete sus datos para obtener un número de atención</p>
            </div>

            <form action="{{ route('turnos.store') }}" method="POST" class="px-8 py-8 space-y-6">
                @csrf

                <div>
                    <label class="block text-bm-navy font-semibold text-sm mb-2">Nombre completo</label>
                    <input
                        type="text"
                        name="nombre"
                        value="{{ old('nombre') }}"
                        placeholder="Ej: María González"
                        class="w-full border-2 border-gray-200 rounded-xl px-4 py-3 text-gray-700 focus:outline-none focus:border-bm-blue transition text-sm"
                        required
                    >
                    @error('nombre')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label class="block text-bm-navy font-semibold text-sm mb-3">Tipo de trámite</label>
                    <div class="grid grid-cols-3 gap-3">
                        @foreach($tipos as $tipo)
                            @php
                                $iconos = ['Caja' => '💰', 'Servicio' => '🛎', 'Creditos' => '💳'];
                                $prefMap = ['Caja' => 'CA', 'Servicio' => 'SC', 'Creditos' => 'CR'];
                            @endphp
                            <label class="cursor-pointer">
                                <input type="radio" name="tipo" value="{{ $tipo }}" class="peer hidden" {{ old('tipo') == $tipo ? 'checked' : '' }} required>
                                <div class="border-2 border-gray-200 peer-checked:border-bm-gold peer-checked:bg-bm-light rounded-xl p-4 text-center transition hover:border-bm-blue">
                                    <span class="text-2xl">{{ $iconos[$tipo] }}</span>
                                    <p class="text-bm-navy font-semibold text-sm mt-1">{{ $tipo }}</p>
                                    <span class="text-xs text-gray-400 font-mono">{{ $prefMap[$tipo] }}</span>
                                </div>
                            </label>
                        @endforeach
                    </div>
                    @error('tipo')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Info cola -->
                <div class="bg-bm-light rounded-xl px-5 py-3 flex items-center justify-between">
                    <span class="text-bm-navy text-sm font-medium">Turnos en espera ahora</span>
                    <span class="text-bm-navy font-black text-2xl">{{ count($cola) }}</span>
                </div>

                <button type="submit" class="w-full bg-bm-navy hover:bg-bm-blue text-white font-bold py-4 rounded-xl transition text-sm tracking-wider uppercase shadow-lg">
                    Obtener Mi Turno
                </button>
            </form>
        </div>

        <!-- Lista de espera rápida -->
        @if(count($cola) > 0)
        <div class="mt-6 bg-white rounded-2xl shadow p-6">
            <h3 class="text-bm-navy font-bold text-sm uppercase tracking-widest mb-4">Cola actual</h3>
            <div class="space-y-2">
                @foreach($cola as $i => $turno)
                <div class="flex items-center gap-3 py-2 {{ $i === 0 ? 'border-l-4 border-bm-gold pl-3' : 'pl-4' }}">
                    <span class="text-xs text-gray-400 w-5">{{ $i + 1 }}</span>
                    <span class="font-mono font-bold text-bm-navy text-sm">{{ $turno['codigo'] }}</span>
                    <span class="text-gray-600 text-sm flex-1">{{ $turno['nombre'] }}</span>
                    <span class="text-xs text-gray-400">{{ $turno['hora'] }}</span>
                </div>
                @endforeach
            </div>
        </div>
        @endif
    </main>
</body>
</html>