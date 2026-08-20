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

test('POST preserves handled Laravel 4xx responses', async () => {
  const originalFetch = globalThis.fetch;
  globalThis.fetch = async () => new Response('Invalid signature', { status: 401 });
  try {
    const response = await worker.fetch(new Request('https://relay.test', {
      method: 'POST', body: 'kr-answer=%7B%7D',
      headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
    }), { RELAY_SECRET: 'relay-test-secret' });
    assert.equal(response.status, 401);
    assert.equal(await response.text(), 'Notification rejected');
  } finally {
    globalThis.fetch = originalFetch;
  }
});

test('POST returns 502 when Laravel has a server error', async () => {
  const originalFetch = globalThis.fetch;
  globalThis.fetch = async () => new Response('Internal error', { status: 500 });
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

test('POST returns 504 when the Laravel request times out', async () => {
  const originalFetch = globalThis.fetch;
  globalThis.fetch = async () => {
    const error = new Error('Timed out');
    error.name = 'AbortError';
    throw error;
  };
  try {
    const response = await worker.fetch(new Request('https://relay.test', {
      method: 'POST', body: 'kr-answer=%7B%7D',
      headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
    }), { RELAY_SECRET: 'relay-test-secret' });
    assert.equal(response.status, 504);
    assert.equal(await response.text(), 'Gateway Timeout');
  } finally {
    globalThis.fetch = originalFetch;
  }
});
