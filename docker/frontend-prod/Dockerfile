# Stage 1: build
FROM oven/bun:1-alpine AS builder
WORKDIR /app

COPY src/frontend/package.json src/frontend/bun.lock* ./
RUN bun install --frozen-lockfile --ignore-scripts

COPY src/frontend/ .
RUN bun scripts/copy-tinymce.js
RUN bun run build:prod

# Stage 2: serve
FROM oven/bun:alpine
WORKDIR /app

COPY --from=builder /app/dist ./dist

RUN echo '{"name":"frontend-serve","private":true,"dependencies":{"serve":"^14"}}' > package.json && \
    bun install

EXPOSE 3000
CMD ["bunx", "serve", "-s", "dist", "-l", "3000"]
