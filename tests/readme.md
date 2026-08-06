# Testes de Integração — Obatala + WordPress Playground

Este documento explica como configurar e rodar os testes de integração do plugin **Obatala** usando o **WordPress Playground CLI** + **Vitest**.

Testes de integração aqui significam: subir um WordPress real (via Playground), com o Obatala e o Tainacan instalados/ativos, e fazer requisições HTTP reais contra a REST API — sem mockar nada do WordPress.

---

## 1. Pré-requisitos

- Node.js 20+
- O repositório clonado localmente

```bash
node -v   # confirme 20+
git clone https://github.com/GomesRita/Obatala.git
cd Obatala
```

---

## 2. Instalação

```bash
npm install
npm install --save-dev @wp-playground/cli vitest
```

---

## 3. Estrutura de arquivos

```
Obatala/
├── classes/                         
├── tests/               
│   ├── Unit/                         ← testes unitários
│   └── Integration/                  ← testes de integração
│       ├── bootstrap.js              ← sobe o Servidor Playground
├── .playground/
│   └── blueprint.json                ← Configurações do ambiente
├── vitest.config.js                  ← Define como e quais testes serão executados
├── phpunit.xml                      
└── package.json                     
```

Convenção de nomenclatura: cada teste espelha o caminho da classe testada, trocando `.php` por `.test.js`.

| Classe | Teste |
|---|---|
| `classes/Api/CustomPostTypeApi.php` | `tests/Integration/Api/CustomPostTypeApi.test.js` |
| `classes/Api/ProcessApi.php` | `tests/Integration/Api/ProcessApi.test.js` |
| `classes/Entities/Process.php` | `tests/Integration/Entities/Process.test.js` |

---

## 4. Arquivos de configuração

### 4.1 `.playground/blueprint.json`

Define o ambiente WordPress: versão do PHP/WP, plugins instalados/ativos e login automático. O Obatala depende do **Tainacan**, então ele é instalado primeiro.

`WP_HOME`/`WP_SITEURL` são fixados explicitamente para evitar loop de redirect canônico do WordPress quando a URL interna do Playground não bate com a porta usada nos testes.

```json
{
  "landingPage": "/wp-admin/",
  "preferredVersions": {
    "php": "8.3",
    "wp": "latest"
  },
  "login": true,
  "steps": [
    {
      "step": "installPlugin",
      "pluginData": { "resource": "wordpress.org/plugins", "slug": "tainacan" }
    },
    { "step": "activatePlugin", "pluginPath": "tainacan/tainacan.php" },
    {
      "step": "activatePlugin",
      "pluginPath": "/wordpress/wp-content/plugins/obatala"
    },
    {
      "step": "defineWpConfigConst",
      "constant": "WP_HOME",
      "value": "http://127.0.0.1:9400"
    },
    {
      "step": "defineWpConfigConst",
      "constant": "WP_SITEURL",
      "value": "http://127.0.0.1:9400"
    }
  ]
}
```

### 4.2 `tests/Integration/bootstrap.js`

Sobe o servidor do Playground uma única vez e expõe a URL real (`serverUrl`) devolvida pelo `runCLI` — evita depender de porta fixa manualmente.

```js
import { runCLI } from '@wp-playground/cli';
import path from 'node:path';

let cliServer;

export async function getPlaygroundServer() {
  if (cliServer) return cliServer;

  cliServer = await runCLI({
    command: 'server',
    mount: [
      {
        hostPath: path.resolve(process.cwd()),
        vfsPath: '/wordpress/wp-content/plugins/obatala',
      },
    ],
    blueprint: path.resolve(process.cwd(), '.playground/blueprint.json'),
  });

  return cliServer;
}

export async function closePlaygroundServer() {
  if (cliServer) {
    await cliServer[Symbol.asyncDispose]?.();
    cliServer = undefined;
  }
}
```

### 4.3 `vitest.config.js`

```js
import { defineConfig } from 'vitest/config';

export default defineConfig({
  test: {
    include: ['tests/Integration/**/*.test.js'],
    testTimeout: 30000,   // tempo máximo de cada teste
    hookTimeout: 60000,   // tempo máximo de beforeAll/afterAll (subir o Playground demora)
    globals: true,
  },
});
```

---

## 5. Rodando

```bash
npm run test:integration
```

Saída esperada:

```
WordPress Playground CLI
PHP 8.3  WordPress latest
Ready! WordPress is running on http://127.0.0.1:xxxx

 ✓ tests/Integration/Api/CustomPostTypeApi.test.js (1 test)

 Test Files  1 passed (1)
      Tests  1 passed (1)
```

---

## 7. Referências oficiais

- [Programmatic Usage of Playground CLI](https://developer.wordpress.org/playground/handbook/guides/programmatic-playground-cli/) — inclui seção "Integration testing with Vitest"
- [Playground CLI reference](https://wordpress.github.io/wordpress-playground/developers/local-development/wp-playground-cli/)
- [Blueprints reference](https://wordpress.github.io/wordpress-playground/) — todos os steps disponíveis
- [E2E Testing with Playwright](https://developer.wordpress.org/playground/handbook/guides/e2e-testing-with-playwright/) — para testes de UI, se necessário no futuro