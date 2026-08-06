import { defineConfig } from 'vitest/config';

export default defineConfig({
  test: {
    include: ['tests/Integration/**/*.test.js'],
    testTimeout: 30000, 
    hookTimeout: 60000,
    globals: true,
  },
});