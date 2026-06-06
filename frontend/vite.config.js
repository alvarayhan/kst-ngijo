import { defineConfig } from 'vite'
import react from '@vitejs/plugin-react'
import tailwindcss from '@tailwindcss/vite' // <-- Import plugin baru

// https://vite.dev/config/
export default defineConfig({
  plugins: [
    react(),
    tailwindcss(), // <-- Pasang di sini
  ],
  server: {
    port: 5174, // Biar gak rebutan sama port Docker lo
  }
})