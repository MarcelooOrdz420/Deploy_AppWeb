const LARAVEL_IPN_URL = 'https://pollos.saborcentral.com/pagos/izipay/ipn';
const TIMEOUT_MS = 15000;

export default {
  async fetch(request, env) {
    const startedAt = Date.now();
    if (request.method === 'GET' || request.method === 'HEAD') {
      return new Response(request.method === 'HEAD' ? null : 'OK', { status: 200 });
    }
    if (request.method !== 'POST') {
      return new Response('Method Not Allowed', { status: 405 });
    }
    if (!env.RELAY_SECRET) {
      console.error(JSON.stringify({ error: 'RELAY_SECRET missing', duration_ms: Date.now() - startedAt }));
      return new Response('Bad Gateway', { status: 502 });
    }

    const controller = new AbortController();
    const timeout = setTimeout(() => controller.abort('timeout'), TIMEOUT_MS);
    try {
      const body = await request.arrayBuffer();
      const headers = new Headers({
        'X-Izipay-Relay': 'cloudflare-worker',
        'X-Relay-Secret': env.RELAY_SECRET,
      });
      const contentType = request.headers.get('Content-Type');
      if (contentType) headers.set('Content-Type', contentType);
      const upstream = await fetch(LARAVEL_IPN_URL, {
        method: 'POST', headers, body, redirect: 'manual', signal: controller.signal,
      });
      console.log(JSON.stringify({ upstream_status: upstream.status, duration_ms: Date.now() - startedAt }));
      if (upstream.status >= 200 && upstream.status < 300) {
        return new Response('OK', { status: 200 });
      }

      // Preserve handled Laravel rejections so the Izipay event history shows
      // the real class of error instead of turning every response into a 502.
      if (upstream.status >= 400 && upstream.status < 500) {
        return new Response('Notification rejected', { status: upstream.status });
      }

      return new Response('Bad Gateway', { status: 502 });
    } catch (error) {
      const timedOut = error?.name === 'AbortError' || controller.signal.aborted;
      console.error(JSON.stringify({
        error: timedOut ? 'upstream_timeout' : String(error?.name || 'upstream_error'),
        duration_ms: Date.now() - startedAt,
      }));
      return new Response(timedOut ? 'Gateway Timeout' : 'Bad Gateway', { status: timedOut ? 504 : 502 });
    } finally {
      clearTimeout(timeout);
    }
  },
};
