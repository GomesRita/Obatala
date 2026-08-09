import { test, expect, beforeAll, beforeEach } from 'vitest';
import { getPlaygroundServer } from '../bootstrap.js';

let baseUrl;
beforeAll(async () => {
  ({ baseUrl } = await getPlaygroundServer());
}, 60000);

const endpoint = (path) => `${baseUrl}/wp-json/obatala/v1/${path}`;

async function criarProcessType(meta = {}) {
  const res = await fetch(`${baseUrl}/wp-json/wp/v2/process_type`, {
    method: 'POST',
    headers: { 'Content-Type': 'application/json' /*, Authorization: authHeader */ },
    body: JSON.stringify({ title: 'Processo Teste', status: 'publish', meta })
  });
  return res.json();
}

async function getMeta(id) {
  return fetch(endpoint(`process_type/${id}/meta`)).then(r => r.json());
}

// --- update_meta / get_meta ---

test('get_meta devolve defaults quando processo não tem meta configurado', async () => {
  const process = await criarProcessType();
  const meta = await getMeta(process.id);

  expect(meta.step_order).toEqual([]);
  expect(meta.flowData).toEqual([]); // repara: default aqui é [] e não {nodes:[],edges:[]}
  expect(meta.accept_attachments).toBe(false);
});

test('update_meta grava flowData enviado como objeto', async () => {
  const process = await criarProcessType();
  const flowData = { nodes: [{ id: 'n1' }], edges: [] };

  const res = await fetch(endpoint(`process_type/${process.id}/meta`), {
    method: 'PUT',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify({ flowData })
  });
  expect(res.ok).toBe(true);

  const meta = await getMeta(process.id);
  expect(meta.flowData.nodes[0].id).toBe('n1');
});

test('update_meta grava step_order e é lido corretamente depois', async () => {
  const process = await criarProcessType();

  await fetch(endpoint(`process_type/${process.id}/meta`), {
    method: 'PUT',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify({ step_order: [3, 1, 2] })
  });

  const meta = await getMeta(process.id);
  expect(meta.step_order).toEqual([3, 1, 2]);
});

// --- assosiate_sector: onde está a lógica de negócio mais densa ---

test('assosiate_sector associa setor e regista no histórico', async () => {
  const process = await criarProcessType();
  await fetch(endpoint(`process_type/${process.id}/meta`), {
    method: 'PUT',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify({ flowData: { nodes: [{ id: 'n1' }], edges: [] } })
  });

  const res = await fetch(endpoint(`process_type/${process.id}/assosiate_sector`), {
    method: 'POST',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify({ sector_id: 'setor-a', node_id: 'n1' })
  });
  expect(res.status).toBe(200);

  const meta = await getMeta(process.id);
  const node = meta.flowData.nodes.find(n => n.id === 'n1');
  expect(node.sector_obatala).toBe('setor-a');
  expect(node.sector_history).toEqual(['setor-a']);
});

test('assosiate_sector não duplica o mesmo setor no histórico', async () => {
  const process = await criarProcessType();
  await fetch(endpoint(`process_type/${process.id}/meta`), {
    method: 'PUT',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify({ flowData: { nodes: [{ id: 'n1' }], edges: [] } })
  });

  const chamar = () => fetch(endpoint(`process_type/${process.id}/assosiate_sector`), {
    method: 'POST',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify({ sector_id: 'setor-a', node_id: 'n1' })
  });

  await chamar();
  await chamar(); // repetido

  const meta = await getMeta(process.id);
  const node = meta.flowData.nodes.find(n => n.id === 'n1');
  expect(node.sector_history).toEqual(['setor-a']); // sem duplicado
});

test('assosiate_sector devolve 404 quando node_id não existe no flowData', async () => {
  const process = await criarProcessType();
  await fetch(endpoint(`process_type/${process.id}/meta`), {
    method: 'PUT',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify({ flowData: { nodes: [{ id: 'outro' }], edges: [] } })
  });

  const res = await fetch(endpoint(`process_type/${process.id}/assosiate_sector`), {
    method: 'POST',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify({ sector_id: 'setor-a', node_id: 'inexistente' })
  });

  expect(res.status).toBe(404);
});

test('assosiate_sector devolve 400 quando flowData não tem nodes configurado', async () => {
  const process = await criarProcessType(); // sem flowData configurado

  const res = await fetch(endpoint(`process_type/${process.id}/assosiate_sector`), {
    method: 'POST',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify({ sector_id: 'setor-a', node_id: 'n1' })
  });

  expect(res.status).toBe(400);
});

test('assosiate_sector devolve 404 quando o processo não existe', async () => {
  const res = await fetch(endpoint(`process_type/999999/assosiate_sector`), {
    method: 'POST',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify({ sector_id: 'setor-a', node_id: 'n1' })
  });

  expect(res.status).toBe(404);
});

test('assosiate_sector rejeita quando sector_id ou node_id em falta (validate_callback)', async () => {
  const process = await criarProcessType();

  const res = await fetch(endpoint(`process_type/${process.id}/assosiate_sector`), {
    method: 'POST',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify({ sector_id: 'setor-a' }) // falta node_id
  });

  expect(res.status).toBe(400);
});