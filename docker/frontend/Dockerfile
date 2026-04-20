# Frontend development — Bun + Vite dev server
FROM oven/bun:1-alpine
WORKDIR /app

COPY src/frontend/package.json src/frontend/bun.lock* ./
RUN bun install --frozen-lockfile --ignore-scripts

COPY src/frontend/ .
RUN bun scripts/copy-tinymce.js

EXPOSE 5174
CMD ["bun", "run", "dev", "--", "--host", "--port", "5174"]
