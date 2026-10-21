<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Panel de seguridad API</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="min-h-screen bg-slate-950 text-slate-100">
    <main class="mx-auto max-w-5xl px-4 py-10">
        <header class="mb-8">
            <p class="text-sm font-semibold uppercase tracking-[0.2em] text-cyan-400">Laravel · API demo</p>
            <h1 class="mt-2 text-3xl font-bold sm:text-4xl">Panel de seguridad</h1>
            <p class="mt-3 max-w-3xl text-slate-300">Prueba JWT, permisos, autorización de documentos y cuotas de peticiones en un entorno local.</p>
        </header>

        <section class="mb-6 rounded-2xl border border-slate-800 bg-slate-900 p-6 shadow-xl">
            <div class="mb-5 flex flex-wrap items-center justify-between gap-3">
                <div>
                    <h2 class="text-xl font-semibold">1. Iniciar sesión</h2>
                    <p class="mt-1 text-sm text-slate-400">El token se guarda en el almacenamiento local del navegador.</p>
                </div>
                <span id="tokenState" class="rounded-full bg-slate-800 px-3 py-1 text-xs text-slate-300">Sin token</span>
            </div>
            <form id="loginForm" class="grid gap-4 sm:grid-cols-[1fr_1fr_auto]">
                <label class="text-sm text-slate-300">
                    Correo
                    <input id="email" type="email" autocomplete="username" required value="admin@seguridad.com"
                        class="mt-1 w-full rounded-lg border border-slate-700 bg-slate-950 px-3 py-2 text-white outline-none focus:border-cyan-400">
                </label>
                <label class="text-sm text-slate-300">
                    Contraseña
                    <input id="password" type="password" autocomplete="current-password" required value="password123"
                        class="mt-1 w-full rounded-lg border border-slate-700 bg-slate-950 px-3 py-2 text-white outline-none focus:border-cyan-400">
                </label>
                <button class="self-end rounded-lg bg-cyan-500 px-5 py-2 font-semibold text-slate-950 hover:bg-cyan-400">Login</button>
            </form>
        </section>

        <section class="mb-6 rounded-2xl border border-slate-800 bg-slate-900 p-6 shadow-xl">
            <h2 class="text-xl font-semibold">2. Probar controles</h2>
            <div class="mt-4 grid gap-4 sm:grid-cols-2">
                <label class="text-sm text-slate-300">
                    ID de documento propio
                    <input id="ownDocumentId" type="number" min="1" value="1"
                        class="mt-1 w-full rounded-lg border border-slate-700 bg-slate-950 px-3 py-2 text-white outline-none focus:border-cyan-400">
                </label>
                <label class="text-sm text-slate-300">
                    ID de otro usuario (prueba BOLA)
                    <input id="otherDocumentId" type="number" min="1" value="2"
                        class="mt-1 w-full rounded-lg border border-slate-700 bg-slate-950 px-3 py-2 text-white outline-none focus:border-cyan-400">
                </label>
            </div>
            <div class="mt-4 flex flex-wrap gap-3">
                <button data-action="catalog" class="rounded-lg bg-indigo-500 px-4 py-2 font-medium hover:bg-indigo-400">GET catálogo (scope)</button>
                <button data-action="own" class="rounded-lg bg-emerald-600 px-4 py-2 font-medium hover:bg-emerald-500">Ver documento propio</button>
                <button data-action="bola" class="rounded-lg bg-amber-600 px-4 py-2 font-medium hover:bg-amber-500">Intentar BOLA</button>
                <button data-action="burst" class="rounded-lg bg-rose-600 px-4 py-2 font-medium hover:bg-rose-500">Ráfaga (15 peticiones)</button>
                <button data-action="logout" class="rounded-lg border border-slate-600 px-4 py-2 font-medium hover:bg-slate-800">Cerrar sesión</button>
            </div>
        </section>

        <section class="rounded-2xl border border-slate-800 bg-slate-900 p-6 shadow-xl">
            <div class="mb-3 flex items-center justify-between">
                <h2 class="text-xl font-semibold">Respuesta</h2>
                <button id="clearOutput" class="text-sm text-slate-400 hover:text-white">Limpiar</button>
            </div>
            <pre id="output" aria-live="polite" class="min-h-32 overflow-x-auto rounded-lg bg-slate-950 p-4 text-sm text-cyan-100">Inicia sesión para probar los endpoints protegidos.</pre>
        </section>
        <p class="mt-5 text-xs leading-relaxed text-slate-500">Solo para desarrollo local. El almacenamiento local es accesible desde JavaScript: en producción considera cookies HttpOnly/Secure/SameSite y configura HTTPS.</p>
    </main>

    <script>
        const API_BASE = `${window.location.origin}/api`;
        const output = document.getElementById('output');
        const tokenState = document.getElementById('tokenState');
        let token = localStorage.getItem('api_jwt') || '';

        function syncTokenState() {
            tokenState.textContent = token ? 'JWT guardado' : 'Sin token';
            tokenState.className = token
                ? 'rounded-full bg-emerald-950 px-3 py-1 text-xs text-emerald-300'
                : 'rounded-full bg-slate-800 px-3 py-1 text-xs text-slate-300';
        }

        function show(data) {
            output.textContent = typeof data === 'string' ? data : JSON.stringify(data, null, 2);
        }

        async function request(path, options = {}) {
            const headers = { Accept: 'application/json', ...options.headers };
            if (token) headers.Authorization = `Bearer ${token}`;
            if (options.body) headers['Content-Type'] = 'application/json';

            const response = await fetch(`${API_BASE}${path}`, { ...options, headers });
            const body = await response.json().catch(() => ({ mensaje: 'Respuesta no JSON' }));
            return { status: response.status, body };
        }

        document.getElementById('loginForm').addEventListener('submit', async (event) => {
            event.preventDefault();
            try {
                const result = await request('/login', {
                    method: 'POST',
                    body: JSON.stringify({
                        email: document.getElementById('email').value,
                        password: document.getElementById('password').value,
                    }),
                });
                if (result.status === 200 && result.body.token) {
                    token = result.body.token;
                    localStorage.setItem('api_jwt', token);
                    syncTokenState();
                }
                const { token: ignoredToken, ...safeBody } = result.body;
                show({ status: result.status, body: safeBody });
            } catch (error) {
                show({ error: error.message });
            }
        });

        document.querySelectorAll('[data-action]').forEach((button) => {
            button.addEventListener('click', async () => {
                const action = button.dataset.action;
                try {
                    if (action === 'catalog') {
                        show(await request('/catalogo'));
                    } else if (action === 'own') {
                        const id = encodeURIComponent(document.getElementById('ownDocumentId').value);
                        show(await request(`/documentos/${id}`));
                    } else if (action === 'bola') {
                        const id = encodeURIComponent(document.getElementById('otherDocumentId').value);
                        show(await request(`/documentos/${id}`));
                    } else if (action === 'burst') {
                        const results = await Promise.all(Array.from({ length: 15 }, () => request('/catalogo')));
                        show({
                            resumen: results.reduce((counts, result) => {
                                counts[result.status] = (counts[result.status] || 0) + 1;
                                return counts;
                            }, {}),
                            respuestas: results,
                        });
                    } else if (action === 'logout') {
                        const result = await request('/logout', { method: 'POST' });
                        token = '';
                        localStorage.removeItem('api_jwt');
                        syncTokenState();
                        show(result);
                    }
                } catch (error) {
                    show({ error: error.message });
                }
            });
        });

        document.getElementById('clearOutput').addEventListener('click', () => show(''));
        syncTokenState();
    </script>
</body>
</html>
