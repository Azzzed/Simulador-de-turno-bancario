<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Banco Mundial – Pantalla de Turnos</title>
    <script src="https://cdn.tailwindcss.com"></script>
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
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;900&display=swap');
        body { font-family: 'Inter', sans-serif; background: #001a33; }
        @keyframes fadeIn { from { opacity:0; transform:translateY(10px); } to { opacity:1; transform:translateY(0); } }
        .fade-in { animation: fadeIn 0.4s ease forwards; }
        @keyframes blink { 0%,100%{opacity:1;} 50%{opacity:0.4;} }
        .blink { animation: blink 1.2s infinite; }
    </style>
</head>
<body class="min-h-screen">

    <!-- Header -->
    <header class="bg-bm-navy border-b-4 border-bm-gold px-8 py-4 flex items-center justify-between">
        <div class="flex items-center gap-4">
            <div class="w-14 h-14 rounded-full bg-bm-gold flex items-center justify-center">
                <span class="text-bm-navy font-black text-xl">BM</span>
            </div>
            <div>
                <h1 class="text-white font-black text-2xl tracking-wide">BANCO MUNDIAL</h1>
                <p class="text-bm-gold text-xs tracking-widest uppercase">Sistema de Atención – Pantalla Pública</p>
            </div>
        </div>
        <div class="text-right">
            <p id="reloj" class="text-white font-mono text-3xl font-bold"></p>
            <p id="fecha" class="text-blue-300 text-xs"></p>
        </div>
    </header>

    <!-- Contenido principal -->
    <div class="px-8 py-8 max-w-7xl mx-auto">
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">

            <!-- TURNO EN ATENCIÓN -->
            <div class="lg:col-span-1">
                <div class="bg-bm-navy rounded-3xl overflow-hidden shadow-2xl border border-blue-800">
                    <div class="bg-bm-gold px-6 py-4">
                        <p class="text-bm-navy font-black text-sm uppercase tracking-widest text-center">🔔 Turno en Atención</p>
                    </div>
                    <div id="turno-actual" class="px-6 py-10 text-center min-h-48 flex flex-col items-center justify-center">
                        <!-- Se actualiza por JS -->
                        @if(session('turno_actual'))
                            @php $ta = session('turno_actual'); @endphp
                            <p class="text-bm-gold font-black text-8xl tracking-widest blink">{{ $ta['codigo'] }}</p>
                            <p class="text-white text-lg mt-2">{{ $ta['nombre'] }}</p>
                            <p class="text-blue-300 text-sm mt-1">{{ $ta['tipo'] }}</p>
                        @else
                            <p class="text-blue-400 text-lg">Sin turno activo</p>
                        @endif
                    </div>
                </div>

                <!-- Estadísticas -->
                <div class="mt-6 grid grid-cols-3 gap-3" id="stats">
                    @php
                        $tipos = ['CA' => 'Caja', 'SC' => 'Servicio', 'CR' => 'Créditos'];
                        $counts = ['CA' => 0, 'SC' => 0, 'CR' => 0];
                        foreach($cola as $t) { $counts[$t['prefijo']] = ($counts[$t['prefijo']] ?? 0) + 1; }
                    @endphp
                    @foreach($tipos as $pref => $label)
                    <div class="bg-bm-navy rounded-2xl p-4 text-center border border-blue-800">
                        <p class="text-bm-gold font-black text-2xl">{{ $counts[$pref] }}</p>
                        <p class="text-blue-300 text-xs mt-1">{{ $label }}</p>
                    </div>
                    @endforeach
                </div>
            </div>

            <!-- COLA DE ESPERA -->
            <div class="lg:col-span-2">
                <div class="bg-bm-navy rounded-3xl overflow-hidden shadow-2xl border border-blue-800">
                    <div class="bg-bm-blue px-6 py-4 flex justify-between items-center">
                        <p class="text-white font-bold text-sm uppercase tracking-widest">👥 Cola de Espera</p>
                        <span id="total-badge" class="bg-bm-gold text-bm-navy font-black text-sm px-3 py-1 rounded-full">
                            {{ count($cola) }} turnos
                        </span>
                    </div>

                    <div id="cola-lista" class="p-6 min-h-64">
                        @if(count($cola) === 0)
                            <div class="text-center py-16">
                                <p class="text-4xl mb-3">✅</p>
                                <p class="text-blue-300 text-lg">No hay turnos en espera</p>
                            </div>
                        @else
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                                @foreach($cola as $i => $turno)
                                <div class="flex items-center gap-4 bg-blue-900 bg-opacity-40 rounded-2xl px-5 py-4 border {{ $i === 0 ? 'border-bm-gold' : 'border-blue-800' }} fade-in">
                                    <span class="text-blue-400 font-bold text-sm w-6">{{ $i + 1 }}</span>
                                    <div class="flex-1">
                                        <p class="text-white font-black text-xl font-mono tracking-wider">{{ $turno['codigo'] }}</p>
                                        <p class="text-blue-300 text-xs mt-0.5">{{ $turno['nombre'] }}</p>
                                    </div>
                                    <div class="text-right">
                                        <span class="text-xs bg-blue-800 text-blue-200 px-2 py-1 rounded-lg">{{ $turno['tipo'] }}</span>
                                        <p class="text-blue-400 text-xs mt-1 font-mono">{{ $turno['hora'] }}</p>
                                    </div>
                                </div>
                                @endforeach
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Reloj
        function actualizarReloj() {
            const ahora = new Date();
            document.getElementById('reloj').textContent = ahora.toLocaleTimeString('es-ES');
            document.getElementById('fecha').textContent = ahora.toLocaleDateString('es-ES', {
                weekday: 'long', year: 'numeric', month: 'long', day: 'numeric'
            });
        }
        actualizarReloj();
        setInterval(actualizarReloj, 1000);

        // Actualización de cola en tiempo real
        async function actualizarCola() {
            try {
                const res = await fetch("{{ route('turnos.cola') }}");
                const data = await res.json();

                // Turno actual
                const divActual = document.getElementById('turno-actual');
                if (data.turnoActual) {
                    divActual.innerHTML = `
                        <p class="text-yellow-400 font-black text-8xl tracking-widest blink">${data.turnoActual.codigo}</p>
                        <p class="text-white text-lg mt-2">${data.turnoActual.nombre}</p>
                        <p class="text-blue-300 text-sm mt-1">${data.turnoActual.tipo}</p>
                    `;
                } else {
                    divActual.innerHTML = '<p class="text-blue-400 text-lg">Sin turno activo</p>';
                }

                // Contadores
                const counts = { CA: 0, SC: 0, CR: 0 };
                data.cola.forEach(t => counts[t.prefijo] = (counts[t.prefijo] || 0) + 1);
                document.getElementById('total-badge').textContent = `${data.cola.length} turnos`;

                // Lista cola
                const lista = document.getElementById('cola-lista');
                if (data.cola.length === 0) {
                    lista.innerHTML = `
                        <div class="text-center py-16">
                            <p class="text-4xl mb-3">✅</p>
                            <p class="text-blue-300 text-lg">No hay turnos en espera</p>
                        </div>`;
                } else {
                    const items = data.cola.map((t, i) => `
                        <div class="flex items-center gap-4 bg-blue-900 bg-opacity-40 rounded-2xl px-5 py-4 border ${i === 0 ? 'border-yellow-400' : 'border-blue-800'} fade-in">
                            <span class="text-blue-400 font-bold text-sm w-6">${i + 1}</span>
                            <div class="flex-1">
                                <p class="text-white font-black text-xl font-mono tracking-wider">${t.codigo}</p>
                                <p class="text-blue-300 text-xs mt-0.5">${t.nombre}</p>
                            </div>
                            <div class="text-right">
                                <span class="text-xs bg-blue-800 text-blue-200 px-2 py-1 rounded-lg">${t.tipo}</span>
                                <p class="text-blue-400 text-xs mt-1 font-mono">${t.hora}</p>
                            </div>
                        </div>
                    `).join('');
                    lista.innerHTML = `<div class="grid grid-cols-1 sm:grid-cols-2 gap-3">${items}</div>`;
                }
            } catch(e) {
                console.error('Error actualizando cola:', e);
            }
        }

        setInterval(actualizarCola, 3000);
    </script>
</body>
</html>