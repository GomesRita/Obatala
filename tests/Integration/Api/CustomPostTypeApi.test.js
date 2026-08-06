import { test, expect, beforeAll } from 'vitest';
import { getPlaygroundServer } from '../bootstrap.js';

let baseUrl;

beforeAll(async () => {
  ({ baseUrl } = await getPlaygroundServer());
}, 60000);

test('debug redirect', async () => {
  const response = await fetch(`${baseUrl}/wp-json/obatala/v1/process_obatala`, {
    redirect: 'manual',
  });
  console.log('status:', response.status);
  console.log('location:', response.headers.get('location'));
});