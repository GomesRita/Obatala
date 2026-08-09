import { runCLI } from '@wp-playground/cli';
import path from 'node:path';

let serverInfo;

export async function getPlaygroundServer() {
  if (serverInfo) return serverInfo;

  serverInfo = await runCLI({
    command: 'server',
    port: 9400,
    mount: [
      {
        hostPath: path.resolve(process.cwd()),
        vfsPath: '/wordpress/wp-content/plugins/obatala',
      },
    ],
    blueprint: path.resolve(process.cwd(), '.playground/blueprint.json'),
    internalCookieStore: true,
  });

  return { baseUrl: 'http://127.0.0.1:9400' };
}