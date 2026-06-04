FROM node:23.10.0

WORKDIR /app

COPY . .

RUN npm ci && npm cache clean --force
RUN npm run build

ENTRYPOINT ["node", ".output/server/index.mjs"]