import { test, expect, beforeAll } from 'vitest';
import { getPlaygroundServer } from '../bootstrap.js';

let baseUrl;

beforeAll(async () => {
  ({ baseUrl } = await getPlaygroundServer());
}, 60000);

const endpoint = (path) => `${baseUrl}/wp-json/obatala/v1/${path}`;

async function fetchJson(path) {
  const response = await fetch(endpoint(path), { redirect: 'manual' });
  const location = response.headers.get('location');

  expect(response.status).toBe(200, `Expected ${path} to return 200, got ${response.status}${location ? ` redirect to ${location}` : ''}`);
  expect(response.ok).toBe(true);
  expect(response.headers.get('content-type')).toMatch(/application\/json/);

  return response.json();
}

test('criar um process_obatala com dados válidos', async () => {
  const response = await fetch(endpoint('process_obatala'), {
    method: 'POST',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify({ title: 'Teste', status: 'publish' /* campos reais do teu schema */ })
  });

  expect(response.status).toBe(201);
  const body = await response.json();
  expect(body.title.rendered).toBe('Teste');
});

