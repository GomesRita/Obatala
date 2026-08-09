import { runCLI } from '@wp-playground/cli';
import path from 'node:path';

(async () => {
  try {
    const serverInfo = await runCLI({
      command: 'server',
      port: 9401,
      mount: [{ hostPath: path.resolve(process.cwd()), vfsPath: '/wordpress/wp-content/plugins/obatala' }],
      blueprint: path.resolve(process.cwd(), '.playground/blueprint.json'),
    });
    console.log('server started');
    const baseUrl = 'http://127.0.0.1:9401';
    try {
      const res = await fetch(`${baseUrl}/wp-json/`);
      console.log('wp-json status', res.status);
      console.log(await res.text());
    } catch (e) {
      console.error('fetch /wp-json failed', e);
    }
    try {
      const res2 = await fetch(`${baseUrl}/wp-json/wp/v2/process_type`, { method: 'GET' });
      console.log('wp/v2/process_type GET', res2.status);
      console.log(await res2.text());
    } catch (e) {
      console.error('fetch process_type failed', e);
    }
  } catch (err) {
    console.error('server error', err);
  }
})();
