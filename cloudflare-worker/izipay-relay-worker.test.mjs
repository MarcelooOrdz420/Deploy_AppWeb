import test from 'node:test';
import assert from 'node:assert/strict';
import worker from './izipay-relay-worker.mjs';

test('GET and HEAD return OK', async () => {
  assert.equal((await worker.fetch(new Request('https://relay.test'), {})).status, 200);
  assert.equal((await worker.fetch(new Request('https://relay.test', { method: 'HEAD' }), {})).status, 200);
});

test('POST preserves bytes and content type and sends secret', async () => {
  const originalFetch = globalThis.fetch;
  const bytes = new Uint8Array([0, 1, 2, 127, 128, 255]);
  let forwarded;
  globalThis.fetch = async (_url, init) => {
    forwarded = init;
    return new Response(null, { status: 204 });
  };
  try {
    const response = await worker.fetch(new Request('https://relay.test', {
      method: 'POST', body: bytes, headers: { 'Content-Type': 'application/octet-stream' },
    }), { RELAY_SECRET: 'relay-test-secret' });
    assert.equal(response.status, 200);
    assert.deepEqual(new Uint8Array(forwarded.body), bytes);
    assert.equal(forwarded.headers.get('Content-Type'), 'application/octet-stream');
    assert.equal(forwarded.headers.get('X-Relay-Secret'), 'relay-test-secret');
    assert.equal(forwarded.redirect, 'manual');
  } finally {
    globalThis.fetch = originalFetch;
  }
});

test('POST returns 502 when Laravel returns non-2xx', async () => {
  const originalFetch = globalThis.fetch;
  globalThis.fetch = async () => new Response('Invalid signature', { status: 401 });
  try {
    const response = await worker.fetch(new Request('https://relay.test', {
      method: 'POST', body: 'kr-answer=%7B%7D',
      headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
    }), { RELAY_SECRET: 'relay-test-secret' });
    assert.equal(response.status, 502);
  } finally {
    globalThis.fetch = originalFetch;
  }
});
